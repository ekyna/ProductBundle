<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes as BStockModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates as BStockStates;
use Ekyna\Bundle\ProductBundle\Form\Type\Inventory\InventoryType;
use Ekyna\Bundle\ProductBundle\Model\InventoryContext;
use Ekyna\Bundle\ProductBundle\Model\InventoryProfiles;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepository;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes as CStockModes;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;
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
 *
 * @TODO    Move to commerce component
 */
class Inventory
{
    const PENDING_DQL = <<<DQL
(
  SELECT SUM(nsoi.quantity) 
  FROM _class_ nsoi
  JOIN nsoi.product nsp
  JOIN nsoi.order nso
  WHERE nsp.subjectIdentity.provider = :provider
    AND nsp.subjectIdentity.identifier = p.id
    AND nso.state = '_state_'
) AS pending
DQL;

    const STOCK_SUB_DQL = <<<DQL
(
    SELECT SUM(_table_._field_)
    FROM _class_ _table_
    WHERE _table_.state <> '_state_'
    AND _table_.product = p.id
) AS _alias_
DQL;

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
     * @var string
     */
    private $supplierOrderItemClass;

    /**
     * @var string
     */
    private $stockUnitClass;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var array
     */
    private $config;

    /**
     * @var InventoryContext
     */
    private $context;

    /**
     * @var FormInterface
     */
    private $form;


    /**
     * Constructor.
     *
     * @param ProductRepository     $productRepository
     * @param ResourceRepository    $supplierProductRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @param TranslatorInterface   $translator
     * @param FormFactory           $formFactory
     * @param SessionInterface      $session
     * @param Formatter             $formatter
     * @param string                $supplierOrderItemClass
     * @param string                $stockUnitClass
     */
    public function __construct(
        ProductRepository $productRepository,
        ResourceRepository $supplierProductRepository,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        FormFactory $formFactory,
        SessionInterface $session,
        Formatter $formatter,
        $supplierOrderItemClass,
        $stockUnitClass
    ) {
        $this->productRepository = $productRepository;
        $this->supplierProductRepository = $supplierProductRepository;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->formatter = $formatter;

        $this->supplierOrderItemClass = $supplierOrderItemClass;
        $this->stockUnitClass = $stockUnitClass;
    }

    /**
     * Returns the form.
     *
     * @param array $options
     *
     * @return FormInterface
     */
    public function getForm(array $options = [])
    {
        if ($this->form) {
            return $this->form;
        }

        return $this->form = $this
            ->formFactory
            ->create(
                InventoryType::class,
                $this->getContext(),
                array_replace(['method' => 'GET'], $options)
            );
    }

    /**
     * Returns the context.
     *
     * @return InventoryContext
     */
    public function getContext()
    {
        if ($this->context) {
            return $this->context;
        }

        $this->context = new InventoryContext();

        if ($this->session->has(static::SESSION_KEY)) {
            $this->context->fromArray(json_decode($this->session->get(static::SESSION_KEY)));
        }

        return $this->context;
    }

    /**
     * Saves the context.
     */
    public function saveContext()
    {
        $this->session->set(static::SESSION_KEY, json_encode($this->getContext()->toArray()));
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

        // Form
        $form = $this->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveContext();
        }

        // Context
        $context = $this->getContext();

        // Products
        $pQb = $this->getProductsQueryBuilder();
        $this->applyContextToQueryBuilder($pQb, $context);

        $page = intval($request->query->get('page', 0));

        $products = $pQb
            ->getQuery()
            ->setFirstResult(30 * $page)
            ->setMaxResults(30)
            ->getScalarResult();

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

            // Format price
            $product['net_price'] = $this->formatter->currency((float)$product['net_price']);

            // Format weight
            $product['weight'] = $this->formatter->number((float)$product['weight']) . '&nbsp;Kg'; // TODO packaging format

            // Format stock
            $product['stock_floor'] = $this->formatter->number((float)$product['stock_floor']);
            $product['in_stock'] = $this->formatter->number((float)$product['in_stock']);
            $product['available_stock'] = $this->formatter->number((float)$product['available_stock']);
            $product['virtual_stock'] = $this->formatter->number((float)$product['virtual_stock']);

            // Eda
            /** @var \DateTime $eda */
            if (null !== $eda = $product['eda']) {
                $product['eda'] = (new \DateTime($product['eda']))->format('d/m/Y'); // TODO localized format
            }

            // Stock themes
            $product['sold_theme'] = '';
            if ($product['sold'] > $product['ordered'] + $product['adjusted']) {
                $product['sold_theme'] = 'danger';
            }

            // Stock sums
            $product['pending'] = 0 < $product['pending'] ? $this->formatter->number((float)$product['pending']) : '';
            $product['ordered'] = $this->formatter->number((float)$product['ordered']);
            $product['received'] = $this->formatter->number((float)$product['received']);
            $product['adjusted'] = $this->formatter->number((float)$product['adjusted']);
            $product['sold'] = $this->formatter->number((float)$product['sold']);
            $product['shipped'] = $this->formatter->number((float)$product['shipped']);

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
                'p.netPrice as net_price',
                'p.weight',
                'p.reference',
                'p.designation',
                'p.attributesDesignation as attributes_designation',
                'p.geocode',
                'p.stockMode as stock_mode',
                'p.stockState as stock_state',
                'p.stockFloor as stock_floor',
                'p.replenishmentTime as replenishment',
                'p.inStock as in_stock',
                'p.availableStock as available_stock',
                'p.virtualStock as virtual_stock',
                'p.estimatedDateOfArrival as eda',
                'parent.designation as parent_designation',
            ])
            ->addSelect($this->getPendingSubQuery())
            ->addSelect($this->buildStockSubQuery('orderedQuantity', 'ordered', 'su1'))
            ->addSelect($this->buildStockSubQuery('receivedQuantity', 'received', 'su2'))
            ->addSelect($this->buildStockSubQuery('adjustedQuantity', 'adjusted', 'su3'))
            ->addSelect($this->buildStockSubQuery('soldQuantity', 'sold', 'su4'))
            ->addSelect($this->buildStockSubQuery('shippedQuantity', 'shipped', 'su5'))
            ->leftJoin('p.brand', 'b')
            ->leftJoin('p.parent', 'parent')
            ->andWhere($pQb->expr()->in('p.type', ':types'))
            ->andWhere($pQb->expr()->not($pQb->expr()->andX(
                $pQb->expr()->eq('p.endOfLife', ':end_of_life'),
                $pQb->expr()->neq('p.virtualStock', ':virtual_stock')

            )))
            ->setParameters([
                'types'         => [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT],
                'provider'      => ProductProvider::NAME,
                'end_of_life'   => true,
                'virtual_stock' => 0,
            ]);

        return $pQb;
    }

    /**
     * Applies the context to the query builder.
     *
     * @param QueryBuilder     $qb
     * @param InventoryContext $context
     */
    private function applyContextToQueryBuilder(QueryBuilder $qb, InventoryContext $context)
    {
        $expr = $qb->expr();

        // Brand filter
        if (0 < $brand = $context->getBrand()) {
            $qb
                ->andWhere($expr->eq('p.brand', ':brand'))
                ->setParameter('brand', $brand);
        }

        // Supplier filter
        if (0 < $supplier = $context->getSupplier()) {
            $qb
                ->andWhere($expr->exists($this->buildSupplierSubQuery()))
                ->setParameter('supplier', $supplier);
        }

        // Designation filter
        if (0 < strlen($designation = $context->getDesignation())) {
            $qb
                ->andWhere($expr->orX(
                    $expr->andX($expr->isNull('p.parent'), $expr->like('p.designation', ':designation')),
                    $expr->andX($expr->isNotNull('p.parent'), $expr->like('parent.designation', ':designation'))
                ))
                ->setParameter('designation', '%' . $designation . '%');
        }

        // Reference filter
        if (0 < strlen($reference = $context->getReference())) {
            $qb
                ->andWhere($expr->like('p.reference', ':reference'))
                ->setParameter('reference', '%' . $reference . '%');
        }

        // Geocode filter
        if (0 < strlen($geocode = $context->getGeocode())) {
            $qb
                ->andWhere($expr->like('p.geocode', ':geocode'))
                ->setParameter('geocode', '%' . $geocode . '%');
        }

        // Mode filter
        if (0 < strlen($mode = $context->getMode())) {
            $qb
                ->andWhere($expr->eq('p.stockMode', ':mode'))
                ->setParameter('mode', $mode);
        }

        // State filter
        if (0 < strlen($state = $context->getState())) {
            $qb
                ->andWhere($expr->eq('p.stockState', ':state'))
                ->setParameter('state', $state);
        }

        // Profile
        if (InventoryProfiles::TREATMENT === $context->getProfile()) {
            $qb->andHaving($expr->andX(
                $expr->lt('shipped', $expr->sum('adjusted', 'received')),
                $expr->lt('shipped', 'sold')
            ));
        } elseif (InventoryProfiles::RESUPPLY === $context->getProfile()) {
            $qb
                ->andWhere($expr->neq('p.stockMode', ':not_mode'))
                ->setParameter('not_mode', CStockModes::MODE_DISABLED)
                ->andHaving($expr->lt($expr->sum('adjusted', 'ordered'), 'sold'));
        } elseif (InventoryProfiles::OUT_OF_STOCK === $context->getProfile()) {
            $qb
                ->andWhere($expr->neq('p.stockMode', ':not_mode'))
                ->setParameter('not_mode', CStockModes::MODE_DISABLED)
                ->andHaving($expr->lt('virtual_stock', 'stock_floor'));
        }

        // Sorting
        $by = $context->getSortBy();
        $dir = strtoupper($context->getSortDir());
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
     * Builds the pending stock sub query.
     *
     * i.e. Ordered quantity of 'new' supplier orders.
     *
     * @return string
     */
    private function getPendingSubQuery()
    {
        return strtr(static::PENDING_DQL, [
            '_class_' => $this->supplierOrderItemClass,
            '_state_' => SupplierOrderStates::STATE_NEW,
        ]);
    }

    /**
     * Builds the stock sub query.
     *
     * @param string $field
     * @param string $fieldAlias
     * @param string $tableAlias
     *
     * @return string
     */
    private function buildStockSubQuery($field, $fieldAlias, $tableAlias)
    {
        return strtr(static::STOCK_SUB_DQL, [
            '_field_' => $field,
            '_class_' => $this->stockUnitClass,
            '_table_' => $tableAlias,
            '_state_' => StockUnitStates::STATE_CLOSED,
            '_alias_' => $fieldAlias,
        ]);
    }

    /**
     * Builds the supplier sub query.
     *
     * @return string
     */
    private function buildSupplierSubQuery()
    {
        $sQb = $this->supplierProductRepository->createQueryBuilder('sp');
        $sQb
            ->select('sp.subjectIdentity.identifier')
            ->andWhere($sQb->expr()->eq('sp.subjectIdentity.identifier', 'p.id'))
            ->andWhere($sQb->expr()->eq('sp.supplier', ':supplier'));

        return $sQb->getDQL();
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

        foreach (BStockModes::getConfig() as $mode => $c) {
            $config['stock_modes'][$mode] = [
                'label' => $this->translator->trans($c[0]),
                'theme' => $c[1],
            ];
        }

        foreach (BStockStates::getConfig() as $state => $c) {
            $config['stock_states'][$state] = [
                'label' => $this->translator->trans($c[0]),
                'theme' => $c[1],
            ];
        }

        $this->config = $config;
    }
}
