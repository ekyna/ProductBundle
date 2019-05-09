<?php

namespace Ekyna\Bundle\ProductBundle\Service\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class AccountLoader
 * @package Ekyna\Bundle\ProductBundle\Service\Routing
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AccountLoader extends Loader
{
    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_replace([
            'catalog' => false,
        ], $config);
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "product account" routes loader twice.');
        }

        $collection = new RouteCollection();

        if ($this->config['catalog']) {
            $routes = $this->import('@EkynaProductBundle/Resources/config/routing/front/account/catalog.yml', 'yaml');
            $routes->addPrefix('/catalogs');

            $collection->addCollection($routes);
        }

        $this->loaded = true;

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'product_account' === $type;
    }
}
