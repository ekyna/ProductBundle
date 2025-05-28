<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes as BStockModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates as BStockStates;
use Ekyna\Bundle\ProductBundle\Entity\ProductBookmark;
use Ekyna\Bundle\ProductBundle\Form\Type\StockView\InventoryType;
use Ekyna\Bundle\ProductBundle\Model\InventoryContext;
use Ekyna\Bundle\ProductBundle\Model\InventoryProfiles;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes as CStockModes;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class StockView
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Move to commerce component
 */
class StockView
{
    use FormatterAwareTrait;

    private const BOOKMARK_SUB_DQL = '(
    SELECT 1
    FROM _class_ bm
    WHERE bm.user = _user_id_
    AND bm.product = p.id
) AS bookmark';

    private const SESSION_KEY = 'inventory_context';

    private ?array            $config  = null;
    private ?InventoryContext $context = null;
    private ?FormInterface    $form    = null;

    public function __construct(
        private readonly StockRepository       $stockRepository,
        private readonly ResourceHelper        $resourceHelper,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface   $translator,
        private readonly FormFactory           $formFactory,
        private readonly RequestStack          $requestStack,
        private readonly UserProviderInterface $userProvider,
        FormatterFactory                       $formatterFactory
    ) {
        $this->setFormatterFactory($formatterFactory);

        $this->loadConfig();
    }

    public function getForm(array $options = []): ?FormInterface
    {
        if ($this->form) {
            return $this->form;
        }

        return $this->form = $this
            ->formFactory
            ->create(
                InventoryType::class,
                $this->getContext(),
                $options
            );
    }

    public function getContext(): ?InventoryContext
    {
        if ($this->context) {
            return $this->context;
        }

        $this->context = new InventoryContext();

        if (($session = $this->getSession()) && $session->has(static::SESSION_KEY)) {
            $this->context->fromArray(json_decode($session->get(static::SESSION_KEY)));
        }

        return $this->context;
    }

    public function saveContext(): void
    {
        if (!$session = $this->getSession()) {
            return;
        }

        $session->set(static::SESSION_KEY, json_encode($this->getContext()->toArray()));
    }

    /**
     * Returns the product list.
     *
     * @param Request $request
     * @param bool    $raw
     * @param array   $options
     *
     * @return array
     */
    public function listProducts(Request $request, bool $raw = false, array $options = []): array
    {
        // Form
        $form = $this->getForm($options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveContext();
        }

        // Context
        $context = $this->getContext();

        // Reference code
        if (null !== $code = $request->query->get('referenceCode')) {
            $context->setReferenceCode($code);
        }

        // Products
        $qb = $this->getProductsQueryBuilder();

        $this->applyContextToQueryBuilder($qb, $context);

        if (null !== $page = $request->query->get('page')) {
            $qb
                ->setFirstResult(30 * intval($page))
                ->setMaxResults(30);
        }

        $products = $qb->getQuery()->getScalarResult();

        if ($raw) {
            return $products;
        }

        return $this->normalizeProducts($products);
    }

    /**
     * Finds the products by ids.
     *
     * @param array $ids
     *
     * @return array
     */
    public function findProducts(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $qb = $this->getProductsQueryBuilder();

        $products = $qb
            ->andWhere($qb->expr()->in('p.id', $ids))
            ->getQuery()
            ->getScalarResult();

        return $this->normalizeProducts($products);
    }

    /**
     * Normalizes the products.
     *
     * @param array $products
     *
     * @return array
     */
    protected function normalizeProducts(array $products): array
    {
        foreach ($products as &$product) {
            $this->normalizeProduct($product);

            unset($product);
        }

        return $products;
    }

    /**
     * Normalizes the product.
     *
     * @param array $product The database product data
     *
     * @return array The normalized product data
     */
    protected function normalizeProduct(array &$product): array
    {
        $formatter = $this->getFormatter();

        // Url
        $product['url'] = $this->urlGenerator->generate($this->config['read_route'], [
            'productId' => $product['id'],
        ]);

        // Format price
        $product['net_price'] = $formatter->currency((float)$product['net_price']);

        // Format weight
        $product['weight'] = $formatter->number((float)$product['weight']) . '&nbsp;Kg'; // TODO packaging format

        // Visible
        $product['visible_label'] = $this->config['bool'][$product['visible']]['label'];
        $product['visible_theme'] = $this->config['bool'][$product['visible']]['theme'];

        // Quote only
        $product['quote_only_label'] = $this->config['bool'][$product['quote_only']]['label'];
        $product['quote_only_theme'] = $this->config['bool'][$product['quote_only']]['theme'];

        // End of life
        $product['end_of_life_label'] = $this->config['bool'][$product['end_of_life']]['label'];
        $product['end_of_life_theme'] = $this->config['bool'][$product['end_of_life']]['theme'];

        // Stock themes
        $product['sold_theme'] = '';
        if ($product['sold'] > $product['ordered'] + $product['adjusted']) {
            $product['sold_theme'] = 'danger';
        }

        // Stock mode badge
        $product['stock_mode_label'] = $this->config['stock_modes'][$product['stock_mode']]['label'];
        $product['stock_mode_theme'] = $this->config['stock_modes'][$product['stock_mode']]['theme'];

        // Stock state badge
        $product['stock_state_label'] = $this->config['stock_states'][$product['stock_state']]['label'];
        $product['stock_state_theme'] = $this->config['stock_states'][$product['stock_state']]['theme'];

        // Format stock
        $product['stock_floor'] = $formatter->number((float)$product['stock_floor']);
        $product['in_stock'] = $formatter->number((float)$product['in_stock']);
        $product['available_stock'] = $formatter->number((float)$product['available_stock']);
        $product['virtual_stock'] = $formatter->number((float)$product['virtual_stock']);

        // Eda
        $product['eda_theme'] = ''; // Move to StockView
        if (null !== $eda = $product['eda']) {
            $eda = new DateTime($eda);
            $product['eda'] = $eda->format('d/m/Y'); // TODO localized format
            if ($eda < (new DateTime())->setTime(0, 0)) {
                $product['eda_theme'] = 'danger';
            }
        }

        // Stock sums
        $product['pending'] = 0 < $product['pending'] ? $formatter->number((float)$product['pending']) : '';
        $product['ordered'] = $formatter->number((float)($product['ordered'] - $product['received']));
        $product['sold'] = $formatter->number((float)($product['sold'] - $product['shipped']));

        $this->stockRepository->normalizeProduct($product);

        return $product;
    }

    private function getProductsQueryBuilder(): QueryBuilder
    {
        $qb = $this->stockRepository->getProductsQueryBuilder();
        $qb->addSelect([
            'p.weight',
            'p.geocode',
            'p.visible',
            'p.quoteOnly as quote_only',
            'p.stockMode as stock_mode',
            'p.stockState as stock_state',
        ]);

        if ($this->userProvider->hasUser()) {
            /** @noinspection PhpParamsInspection */
            $qb->addSelect($this->buildBookmarkSubQuery($this->userProvider->getUser()));
        }

        return $qb;
    }

    /**
     * Applies the context to the query builder.
     *
     * @param QueryBuilder     $qb
     * @param InventoryContext $context
     */
    private function applyContextToQueryBuilder(QueryBuilder $qb, InventoryContext $context): void
    {
        $expr = $qb->expr();

        if (!empty($code = $context->getReferenceCode())) {
            $qb
                ->join('p.references', 'r')
                ->andWhere($qb->expr()->eq('r.code', ':code'))
                ->setParameter('code', $code);

            return;
        }

        // Brand filter
        if (0 < $brand = $context->getBrand()) {
            $qb
                ->andWhere($expr->eq('p.brand', ':brand'))
                ->setParameter('brand', $brand);
        }

        // Supplier filter
        if (0 < $supplier = $context->getSupplier()) {
            $qb
                ->andWhere($expr->exists($this->stockRepository->buildSupplierSubQuery()))
                ->setParameter('supplier', $supplier);
        }

        // Designation filter
        if (!empty($designation = $context->getDesignation())) {
            $qb
                ->andWhere(
                    $expr->orX(
                        $expr->andX($expr->isNull('p.parent'), $expr->like('p.designation', ':designation')),
                        $expr->andX($expr->isNotNull('p.parent'), $expr->like('parent.designation', ':designation'))
                    )
                )
                ->setParameter('designation', '%' . $designation . '%');
        }

        // Reference filter
        if (!empty($reference = $context->getReference())) {
            $qb
                ->andWhere($expr->like('p.reference', ':reference'))
                ->setParameter('reference', '%' . $reference . '%');
        }

        // Geocode filter
        if (!empty($geocode = $context->getGeocode())) {
            $qb
                ->andWhere($expr->like('p.geocode', ':geocode'))
                ->setParameter('geocode', '%' . $geocode . '%');
        }

        // Visible
        if (!is_null($value = $context->isVisible())) {
            $qb
                ->andWhere($qb->expr()->eq('p.visible', ':visible'))
                ->setParameter('visible', $value);
        }

        // Quote only
        if (!is_null($value = $context->isQuoteOnly())) {
            $qb
                ->andWhere($qb->expr()->eq('p.quoteOnly', ':quote_only'))
                ->setParameter('quote_only', $value);
        }

        // End of life
        if (!is_null($value = $context->isEndOfLife())) {
            $qb
                ->andWhere($qb->expr()->eq('p.endOfLife', ':end_of_life'))
                ->setParameter('end_of_life', $value);
        }

        // Mode filter
        if (!empty($mode = $context->getMode())) {
            $qb
                ->andWhere($expr->eq('p.stockMode', ':mode'))
                ->setParameter('mode', $mode);
        }

        // State filter
        if (!empty($state = $context->getState())) {
            $qb
                ->andWhere($expr->eq('p.stockState', ':state'))
                ->setParameter('state', $state);
        }

        // Bookmark
        if (!is_null($bookmark = $context->isBookmark()) && $this->userProvider->hasUser()) {
            if ($bookmark) {
                $qb
                    ->andHaving($qb->expr()->eq('bookmark', ':bookmark'))
                    ->setParameter('bookmark', 1);
            } else {
                $qb->andHaving($qb->expr()->isNull('bookmark'));
            }
        }

        // Profile
        if (InventoryProfiles::TREATMENT === $context->getProfile()) {
            $qb->andHaving(
                $expr->andX(
                    $expr->lt('shipped', $expr->sum('adjusted', 'received')),
                    $expr->lt('shipped', 'sold')
                )
            );
        } elseif (InventoryProfiles::RESUPPLY === $context->getProfile()) {
            $qb
                ->andWhere($expr->neq('p.stockMode', ':not_mode'))
                ->setParameter('not_mode', CStockModes::MODE_DISABLED)
                ->andHaving($expr->lt($expr->sum('adjusted', 'ordered'), 'sold'));
        } elseif (InventoryProfiles::OUT_OF_STOCK === $context->getProfile()) {
            $qb
                ->andWhere($expr->neq('p.stockMode', ':not_mode'))
                ->setParameter('not_mode', CStockModes::MODE_DISABLED)
                ->andHaving(
                    $qb->expr()->orX(
                        $qb->expr()->orX(
                            $qb->expr()->andX(
                                $qb->expr()->eq('p.endOfLife', 0),
                                $qb->expr()->lt('p.virtualStock', 'p.stockFloor')
                            ),
                            $qb->expr()->andX(
                                $qb->expr()->eq('p.endOfLife', 1),
                                $qb->expr()->lt('p.virtualStock', 0)
                            )
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->isNotNull('p.estimatedDateOfArrival'),
                            $qb->expr()->lte('p.estimatedDateOfArrival', ':today')
                        )
                    )
                )
                ->setParameter('today', (new DateTime())->setTime(0, 0), Types::DATE_MUTABLE);
        } elseif (InventoryProfiles::ORDERED === $context->getProfile()) {
            $qb
                ->andWhere($expr->neq('p.stockMode', ':not_mode'))
                ->setParameter('not_mode', CStockModes::MODE_DISABLED)
                ->andHaving($expr->gt('virtual_stock', 'stock_floor'))
                ->andHaving($expr->gt('ordered', 'received'));
        }

        // Sorting
        $by = $context->getSortBy();
        $dir = strtoupper((string)$context->getSortDir());
        if (!empty($by) && in_array($dir, ['ASC', 'DESC'])) {
            if ($by === 'brand') {
                $by = 'b.name';
            } else {
                $by = 'p.' . $by;
            }
        } else {
            $by = 'p.id';
            $dir = 'DESC';
        }
        $qb->addOrderBy($by, $dir);
    }

    /**
     * Builds the bookmark sub query.
     *
     * @param UserInterface $user
     *
     * @return string
     */
    private function buildBookmarkSubQuery(UserInterface $user): string
    {
        return strtr(static::BOOKMARK_SUB_DQL, [
            '_class_'   => ProductBookmark::class,
            '_user_id_' => $user->getId(),
        ]);
    }

    private function loadConfig(): void
    {
        if ($this->config) {
            return;
        }

        $config = [
            'stock_modes'  => [],
            'stock_states' => [],
            'bool'         => [
                true  => [
                    'label' => $this->translator->trans('value.yes', [], 'EkynaUi'),
                    'theme' => 'success',
                ],
                false => [
                    'label' => $this->translator->trans('value.no', [], 'EkynaUi'),
                    'theme' => 'danger',
                ],
            ],
            'read_route'   => $this->resourceHelper->getRoute('ekyna_product.product', ReadAction::class),
        ];

        foreach (BStockModes::getConfig() as $mode => $c) {
            $config['stock_modes'][$mode] = [
                'label' => $this->translator->trans($c[0], [], 'EkynaCommerce'),
                'theme' => $c[1],
            ];
        }

        foreach (BStockStates::getConfig() as $state => $c) {
            $config['stock_states'][$state] = [
                'label' => $this->translator->trans($c[0], [], 'EkynaCommerce'),
                'theme' => $c[1],
            ];
        }

        $this->config = $config;
    }

    private function getSession(): ?SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            return null;
        }
    }
}
