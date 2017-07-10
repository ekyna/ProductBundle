<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates;
use Ekyna\Bundle\ProductBundle\Form\Type\InventorySearchType;
use Ekyna\Bundle\ProductBundle\Model\InventorySearch;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepository;
use Ekyna\Bundle\ProductBundle\Repository\ProductStockUnitRepository;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Inventory
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Inventory
{
    const SESSION_KEY = 'inventory_context';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ResourceRepository
     */
    private $supplierProductRepository;

    /**
     * @var ProductStockUnitRepository
     */
    private $stockUnitRepository;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var \NumberFormatter
     */
    private $formatter;

    /**
     * @var array
     */
    private $config;

    /**
     * @var InventorySearch
     */
    private $searchData;

    /**
     * @var FormInterface
     */
    private $searchForm;


    /**
     * Constructor.
     *
     * @param ProductRepository          $productRepository
     * @param ResourceRepository         $supplierProductRepository
     * @param ProductStockUnitRepository $stockUnitRepository
     * @param UrlGeneratorInterface      $urlGenerator
     * @param TranslatorInterface        $translator
     * @param FormFactory                $formFactory
     * @param SessionInterface           $session
     * @param LocaleProviderInterface    $localeProvider
     */
    public function __construct(
        ProductRepository $productRepository,
        ResourceRepository $supplierProductRepository,
        ProductStockUnitRepository $stockUnitRepository,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        FormFactory $formFactory,
        SessionInterface $session,
        LocaleProviderInterface $localeProvider
    ) {
        $this->productRepository = $productRepository;
        $this->supplierProductRepository = $supplierProductRepository;
        $this->stockUnitRepository = $stockUnitRepository;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->session = $session;

        $this->formatter = \NumberFormatter::create(
            $localeProvider->getCurrentLocale(),
            \NumberFormatter::TYPE_DEFAULT
        );
    }

    /**
     * Returns the search form.
     *
     * @return FormInterface
     */
    public function getSearchForm()
    {
        if ($this->searchForm) {
            return $this->searchForm;
        }

        return $this->searchForm = $this
            ->formFactory
            ->create(
                InventorySearchType::class,
                $this->getSearchData(),
                ['method' => 'GET']
            );
    }

    /**
     * Returns the search data.
     *
     * @return InventorySearch
     */
    public function getSearchData()
    {
        if ($this->searchData) {
            return $this->searchData;
        }

        $this->searchData = new InventorySearch();

        if ($this->session->has(static::SESSION_KEY)) {
            $this->searchData->fromArray(json_decode($this->session->get(static::SESSION_KEY)));
        }

        return $this->searchData;
    }

    /**
     * Saves the search data.
     */
    public function saveSearchData()
    {
        $this->session->set(static::SESSION_KEY, json_encode($this->getSearchData()->toArray()));
    }

    /**
     * Returns the product list.
     *
     * @param Request $request
     *
     * @return array
     */
    public function listProducts(Request $request)
    {
        $this->loadConfig();

        // Search form
        $form = $this->getSearchForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveSearchData();
        }

        // Search parameters
        $search = $this->getSearchData();

        // Products
        $pQb = $this->getProductsQueryBuilder();
        $this->applySearchData($pQb, $search);

        $products = $pQb->getQuery()->setMaxResults(30)->getScalarResult();

        // Stock
        $sQb = $this->getStockDataQueryBuilder()->getQuery();

        foreach ($products as &$product) {
            // Designation (for variant)
            if ($product['type'] === ProductTypes::TYPE_VARIANT) {
                if (0 == strlen($product['designation'])) {
                    $product['designation'] = sprintf(
                        '%s %s',
                        $product['parent_designation'],
                        $product['attributes_designation']
                    );
                }
            }

            // Url
            $product['url'] = $this->urlGenerator->generate('ekyna_product_product_admin_show', [
                'productId' => $product['id'],
            ]);

            // Format stock
            $product['in_stock'] = $this->formatter->format($product['in_stock']);
            $product['virtual_stock'] = $this->formatter->format($product['virtual_stock']);

            // Eda
            /** @var \DateTime $eda */
            if (null !== $eda = $product['eda']) {
                $product['eda'] = $eda->format('d/m/Y'); // TODO localized format
            }

            // Query stock data
            $stock = $sQb
                ->setParameter('product', $product['id'])
                ->getScalarResult()[0];

            // Stock sums
            $product['ordered'] = $this->formatter->format($stock['ordered']);
            $product['received'] = $this->formatter->format($stock['received']);
            $product['sold'] = $this->formatter->format($stock['sold']);
            $product['shipped'] = $this->formatter->format($stock['shipped']);

            // Stock themes
            $product['sold_theme'] = '';
            if ($product['sold'] > $product['ordered']) {
                $product['sold_theme'] = 'danger';
            }

            // Stock mode badge
            $product['stock_mode_label'] = $this->config['stock_modes'][$product['stock_mode']]['label'];
            $product['stock_mode_theme'] = $this->config['stock_modes'][$product['stock_mode']]['theme'];

            // Stock state badge
            $product['stock_state_label'] = $this->config['stock_states'][$product['stock_state']]['label'];
            $product['stock_state_theme'] = $this->config['stock_states'][$product['stock_state']]['theme'];

            // Cleanup
            unset($product['parent_id']);
            unset($product['parent_designation']);
            unset($product['attributes_designation']);

            unset($product);
        }

        return $products;
    }

    /**
     * Returns the products query builder.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getProductsQueryBuilder()
    {
        $pQb = $this->productRepository->createQueryBuilder('p');
        $pQb
            ->select([
                'p.id',
                'p.type',
                'b.name as brand',
                'p.reference',
                'p.designation',
                'p.attributesDesignation as attributes_designation',
                'p.geocode',
                'p.stockMode as stock_mode',
                'p.stockState as stock_state',
                'p.inStock as in_stock',
                'p.virtualStock as virtual_stock',
                'p.estimatedDateOfArrival as eda',
                'parent.designation as parent_designation',
            ])
            ->leftJoin('p.brand', 'b')
            ->leftJoin('p.parent', 'parent')
            ->andWhere($pQb->expr()->in('p.type', ':types'))
            ->setParameter('types', [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT]);

        return $pQb;
    }

    /**
     * Applies the search data to the query builder.
     *
     * @param QueryBuilder    $qb
     * @param InventorySearch $search
     */
    private function applySearchData(QueryBuilder $qb, InventorySearch $search)
    {
        $expr = $qb->expr();

        // Brand filter
        if (0 < $brand = $search->getBrand()) {
            $qb
                ->andWhere($expr->eq('p.brand', ':brand'))
                ->setParameter('brand', $brand);
        }

        // Supplier filter
        if (0 < $supplier = $search->getSupplier()) {
            $qb
                ->andWhere($expr->exists($this->getSupplierSubQuery()))
                ->setParameter('supplier', $supplier);
        }

        // Designation filter
        if (0 < strlen($designation = $search->getDesignation())) {
            $qb
                ->andWhere($expr->orX(
                    $expr->andX($expr->isNull('p.parent'), $expr->like('p.designation', ':designation')),
                    $expr->andX($expr->isNotNull('p.parent'), $expr->like('parent.designation', ':designation'))
                ))
                ->setParameter('designation', '%' . $designation . '%');
        }

        // Reference filter
        if (0 < strlen($reference = $search->getReference())) {
            $qb
                ->andWhere($expr->like('p.reference', ':reference'))
                ->setParameter('reference', '%' . $reference . '%');
        }

        // Geocode filter
        if (0 < strlen($geocode = $search->getGeocode())) {
            $qb
                ->andWhere($expr->like('p.geocode', ':geocode'))
                ->setParameter('geocode', '%' . $geocode . '%');
        }

        // Mode filter
        if (0 < strlen($mode = $search->getMode())) {
            $qb
                ->andWhere($expr->like('p.stockMode', ':mode'))
                ->setParameter('mode', '%' . $mode . '%');
        }

        // State filter
        if (0 < strlen($state = $search->getState())) {
            $qb
                ->andWhere($expr->like('p.stockState', ':state'))
                ->setParameter('state', '%' . $state . '%');
        }

        // Sorting
        $by = $search->getSortBy();
        $dir = strtoupper($search->getSortDir());
        if (0 < strlen($by) && in_array($dir, ['ASC', 'DESC'])) {
            if ($by === 'brand') {
                $by = 'b.name';
            } else {
                $by = 'p.' . $by;
            }
            $qb->addOrderBy($by, $dir);
        }
    }

    /**
     * Returns the supplier sub query's DQL.
     *
     * @return string
     */
    private function getSupplierSubQuery()
    {
        $sQb = $this->supplierProductRepository->createQueryBuilder('sp');
        $sQb
            ->select('sp.subjectIdentity.identifier')
            ->andWhere($sQb->expr()->eq('sp.subjectIdentity.identifier', 'p.id'))
            ->andWhere($sQb->expr()->eq('sp.supplier', ':supplier'));

        return $sQb->getDQL();
    }

    /**
     * Returns the stock units query builder.
     *
     * @return QueryBuilder
     */
    private function getStockDataQueryBuilder()
    {
        $qb = $this->stockUnitRepository->createQueryBuilder('s');

        $expr = $qb->expr();

        return $qb
            ->select([
                'SUM(s.orderedQuantity) AS ordered',
                'SUM(s.receivedQuantity) AS received',
                'SUM(s.soldQuantity) AS sold',
                'SUM(s.shippedQuantity) AS shipped',
            ])
            ->andWhere($expr->neq('s.state', ':state'))
            ->andWhere($expr->eq('s.product', ':product'))
            ->setParameter('state', StockUnitStates::STATE_CLOSED);
    }

    /**
     * Loads the config.
     */
    private function loadConfig()
    {
        if ($this->config) {
            return;
        }

        $config = [
            'stock_modes'  => [],
            'stock_states' => [],
        ];

        foreach (StockSubjectModes::getConfig() as $mode => $c) {
            $config['stock_modes'][$mode] = [
                'label' => $this->translator->trans($c[0]),
                'theme' => $c[1],
            ];
        }

        foreach (StockSubjectStates::getConfig() as $state => $c) {
            $config['stock_states'][$state] = [
                'label' => $this->translator->trans($c[0]),
                'theme' => $c[1],
            ];
        }

        $this->config = $config;
    }
}
