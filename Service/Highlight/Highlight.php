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
use Twig\Environment;

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
     * @var Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $config;


    public function __construct(
        ContextProviderInterface   $contextProvider,
        CartProviderInterface      $cartProvider,
        ProductRepositoryInterface $productRepository,
        StatCountRepository        $countRepository,
        StatCrossRepository        $crossRepository,
        Environment                $twig,
        array                      $config = []
    ) {
        $this->contextProvider = $contextProvider;
        $this->cartProvider = $cartProvider;
        $this->productRepository = $productRepository;
        $this->countRepository = $countRepository;
        $this->crossRepository = $crossRepository;
        $this->twig = $twig;

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
     * @param array $parameters
     *
     * @return ProductInterface[]|int[]
     */
    public function getBestSellers(array $parameters = []): array
    {
        if (!$this->config['enabled']) {
            return [];
        }

        $parameters = array_replace($this->countRepository->getFindProductsDefaultParameters(), [
            'from'  => $this->config['best_seller']['from'],
            'limit' => $this->config['best_seller']['limit'],
        ], $parameters);

        if (empty($parameters['exclude'])) {
            $parameters['exclude'] = $this->getCartProductIds();
        }

        $results = [];

        // MODE Always
        foreach ($this->productRepository->findBestSellers($parameters) as $result) {
            $results[] = $result;
            $parameters['exclude'][] = $parameters['id_only'] ? $result : $result->getId();
        }
        $parameters['limit'] -= count($results);
        if (0 >= $parameters['limit']) {
            return $results;
        }

        // MODE Auto
        if (false === $parameters['group']) {
            $parameters['group'] = null;
        } elseif (!$parameters['group'] instanceof CustomerGroupInterface) {
            $parameters['group'] = $this
                ->contextProvider
                ->getContext()
                ->getCustomerGroup();
        }
        foreach ($this->countRepository->findProducts($parameters) as $result) {
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Renders the best sellers products.
     *
     * @param array $parameters
     * @param array $options
     *
     * @return string
     */
    public function renderBestSellers(array $parameters = [], array $options = [])
    {
        $products = $this->getBestSellers($parameters);

        if (empty($products)) {
            return '';
        }

        $options = array_replace(
            [
                'template'       => $this->config['best_seller']['template'],
                'thumb_template' => $this->config['thumb_template'],
                'nb_per_row'     => 4,
            ],
            $options,
            [
                'products' => $products,
            ]
        );

        return $this->twig->render($options['template'], $options);
    }

    /**
     * Returns the cross selling products.
     *
     * @param array $parameters
     *
     * @return ProductInterface[]
     */
    public function getCrossSelling(array $parameters = []): array
    {
        if (!$this->config['enabled']) {
            return [];
        }

        $parameters = array_replace($this->crossRepository->getFindProductsDefaultParameters(), [
            'from'  => $this->config['cross_selling']['from'],
            'limit' => $this->config['cross_selling']['limit'],
        ], $parameters);

        if (empty($parameters['exclude'])) {
            $parameters['exclude'] = $this->getCartProductIds();
        }

        // MODE Always
        $results = [];
        foreach ($this->productRepository->findCrossSelling($parameters) as $result) {
            $results[] = $result;
            $parameters['exclude'][] = $parameters['id_only'] ? $result : $result->getId();
        }
        $parameters['limit'] -= count($results);
        if (0 >= $parameters['limit']) {
            return $results;
        }

        // PRODUCT configuration
        if ($parameters['source'] instanceof ProductInterface) {
            foreach ($parameters['source']->getCrossSellings() as $crossSelling) {
                $target = $crossSelling->getTarget();
                if (!in_array($target->getId(), $parameters['exclude'])) {
                    $results[] = $parameters['id_only'] ? $target->getId() : $target;
                    $parameters['limit']--;
                    if (0 >= $parameters['limit']) {
                        break;
                    }
                }
            }
        }
        if (0 >= $parameters['limit']) {
            return $results;
        }

        // MODE Auto
        if (null === $parameters['source']) {
            $parameters['source'] = $parameters['exclude'];
        }
        if (empty($parameters['source'])) {
            return $results;
        }
        if (false === $parameters['group']) {
            $parameters['group'] = null;
        } elseif (!$parameters['group'] instanceof CustomerGroupInterface) {
            $parameters['group'] = $this
                ->contextProvider
                ->getContext()
                ->getCustomerGroup();
        }
        foreach ($this->crossRepository->findProducts($parameters) as $p) {
            $results[] = $p;
        }

        return $results;
    }

    /**
     * Renders the cross selling products.
     *
     * @param array $parameters
     * @param array $options
     *
     * @return string
     */
    public function renderCrossSelling(array $parameters = [], array $options = [])
    {
        $products = $this->getCrossSelling($parameters);

        if (empty($products)) {
            return '';
        }

        $options = array_replace(
            [
                'template'       => $this->config['cross_selling']['template'],
                'thumb_template' => $this->config['thumb_template'],
                'nb_per_row'     => 4,
            ],
            $options,
            [
                'products' => $products,
            ]
        );

        return $this->twig->render($options['template'], $options);
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
                    $id = $item->getSubjectIdentity()->getIdentifier();
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
