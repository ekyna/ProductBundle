<?php

namespace Ekyna\Bundle\ProductBundle\Service\Highlight;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;
use Ekyna\Bundle\ProductBundle\Repository\StatCrossRepository;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class Highlight
 * @package Ekyna\Bundle\ProductBundle\Service\Highlight
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Highlight
{
    /**
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * @var CartProviderInterface
     */
    private $cartProvider;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StatCountRepository
     */
    private $countRepository;

    /**
     * @var StatCrossRepository
     */
    private $crossRepository;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface   $contextProvider
     * @param CartProviderInterface      $cartProvider
     * @param ProductRepositoryInterface $productRepository
     * @param StatCountRepository        $countRepository
     * @param StatCrossRepository        $crossRepository
     * @param EngineInterface            $templating
     * @param array                      $config
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        CartProviderInterface $cartProvider,
        ProductRepositoryInterface $productRepository,
        StatCountRepository $countRepository,
        StatCrossRepository $crossRepository,
        EngineInterface $templating,
        array $config = []
    ) {
        $this->contextProvider   = $contextProvider;
        $this->cartProvider      = $cartProvider;
        $this->productRepository = $productRepository;
        $this->countRepository   = $countRepository;
        $this->crossRepository   = $crossRepository;
        $this->templating        = $templating;

        $this->config = array_replace([
            'thumb_template' => '@EkynaProduct/Highlight/thumb.html.twig',
            'best_seller'    => [
                'template' => '@EkynaProduct/Highlight/best.html.twig',
                'from'     => '-6 months',
                'limit'    => 8,
            ],
            'cross_selling'  => [
                'template' => '@EkynaProduct/Highlight/cross.html.twig',
                'from'     => '-6 months',
                'limit'    => 4,
            ],
        ], $config);
    }

    /**
     * Returns the best sellers products.
     *
     * @param CustomerGroupInterface|false|null $group
     * @param int                               $limit
     * @param string                            $from   The start date
     * @param bool                              $idOnly Whether to return product ids only
     *
     * @return ProductInterface[]|int[]
     */
    public function getBestSellers($group = null, int $limit = null, string $from = null, bool $idOnly = false): array
    {
        if (!$this->config['enabled']) {
            return [];
        }

        if (0 >= $limit) {
            $limit = $this->config['best_seller']['limit'];
        }

        $cartProductIds = $this->getCartProductIds();

        $bestSellers = $this->productRepository->findBestSellers($limit, $cartProductIds, $idOnly);

        if ($limit > $count = count($bestSellers)) {
            if (false === $group) {
                $group = null;
            } elseif (!$group instanceof CustomerGroupInterface) {
                $group = $this
                    ->contextProvider
                    ->getContext()
                    ->getCustomerGroup();
            }

            if (empty($from)) {
                $from = $this->config['best_seller']['from'];
            }

            $products = $this
                ->countRepository
                ->findProducts($group, new \DateTime($from), $limit - $count, $cartProductIds, $idOnly);

            foreach ($products as $p) {
                $bestSellers[] = $p;
            }
        }

        return $bestSellers;
    }

    /**
     * Renders the best sellers products.
     *
     * @param array $options
     *
     * @return string
     */
    public function renderBestSellers(array $options = [])
    {
        $options = array_replace(
            $this->config['best_seller'],
            [
                'thumb_template' => $this->config['thumb_template'],
                'group'          => null,
                'nb_per_row'     => 4,
            ],
            $options
        );

        $products = $this->getBestSellers($options['group'], $options['limit'], $options['from']);

        if (empty($products)) {
            return '';
        }

        return $this->templating->render($options['template'], [
            'products'       => $products,
            'thumb_template' => $options['thumb_template'],
            'nb_per_row'     => $options['nb_per_row'],
        ]);
    }

    /**
     * Returns the cross selling products.
     *
     * @param ProductInterface|null             $product The reference product
     * @param CustomerGroupInterface|false|null $group   The customer group
     * @param int                               $limit   The number od products to return
     * @param string                            $from    The start date
     * @param bool                              $idOnly  Whether to return product ids only
     *
     * @return ProductInterface[]
     */
    public function getCrossSelling(
        ProductInterface $product = null,
        $group = null,
        int $limit = null,
        string $from = null,
        bool $idOnly = false
    ): array {
        if (!$this->config['enabled']) {
            return [];
        }

        if (0 >= $limit) {
            $limit = $this->config['cross_selling']['limit'];
        }

        $result         = [];
        $cartProductIds = $this->getCartProductIds();

        if ($product) {
            foreach ($product->getCrossSellings() as $crossSelling) {
                $target = $crossSelling->getTarget();
                if (!in_array($target->getId(), $cartProductIds)) {
                    $result[] = $idOnly ? $target->getId() : $target;
                    $limit--;
                    if (0 >= $limit) {
                        break;
                    }
                }
            }
        }

        if (0 < $limit) {
            $products = $this->productRepository->findCrossSelling($limit, $cartProductIds, $idOnly);
            foreach ($products as $p) {
                $result[] = $p;
                $limit--;
                if (0 >= $limit) {
                    break;
                }
            }
        }

        if (0 < $limit) {
            if (null === $product) {
                $product = $cartProductIds;
            }
            if (empty($product)) {
                return $result;
            }

            if (false === $group) {
                $group = null;
            } elseif (!$group instanceof CustomerGroupInterface) {
                $group = $this
                    ->contextProvider
                    ->getContext()
                    ->getCustomerGroup();
            }

            if (empty($from)) {
                $from = $this->config['cross_selling']['from'];
            }

            $products = $this
                ->crossRepository
                ->findProducts($product, $group, new \DateTime($from), $limit, $cartProductIds, $idOnly);

            foreach ($products as $p) {
                $result[] = $p;
            }
        }

        return $result;
    }

    /**
     * Renders the cross selling products.
     *
     * @param ProductInterface $product
     * @param array            $options
     *
     * @return string
     */
    public function renderCrossSelling(ProductInterface $product = null, array $options = [])
    {
        $options = array_replace(
            $this->config['cross_selling'],
            [
                'thumb_template' => $this->config['thumb_template'],
                'group'          => null,
                'nb_per_row'     => 4,
            ],
            $options
        );

        $products = $this->getCrossSelling($product, $options['group'], $options['limit'], $options['from']);

        if (empty($products)) {
            return '';
        }

        return $this->templating->render($options['template'], [
            'products'       => $products,
            'thumb_template' => $options['thumb_template'],
            'nb_per_row'     => $options['nb_per_row'],
        ]);
    }

    /**
     * Returns the cart's products ids.
     *
     * @return array
     */
    protected function getCartProductIds()
    {
        $ids = [];

        if (!$this->cartProvider->hasCart()) {
            return $ids;
        }

        $cart = $this->cartProvider->getCart();

        $this->addItemsProductIds($cart->getItems()->toArray(), $ids);

        return $ids;
    }

    /**
     * Adds the sale items products ids.
     *
     * @param \Ekyna\Component\Commerce\Common\Model\SaleItemInterface[] $items
     * @param array                                                      $ids
     */
    protected function addItemsProductIds(array $items, array &$ids)
    {
        foreach ($items as $item) {
            if ($item->isPrivate()) {
                continue;
            }

            if ($item->hasSubjectIdentity()) {
                if ($item->getSubjectIdentity()->getProvider() === ProductProvider::NAME) {
                    $id = intval($item->getSubjectIdentity()->getIdentifier());
                    if (!in_array($id, $ids, true)) {
                        $ids[] = $id;
                    }
                }
            }

            if ($item->hasChildren()) {
                $this->addItemsProductIds($item->getChildren()->toArray(), $ids);
            }
        }
    }
}
