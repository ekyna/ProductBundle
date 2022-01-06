<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Routing;

use Ekyna\Bundle\ResourceBundle\Service\Routing\Traits\PrefixTrait;
use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class AccountLoader
 * @package Ekyna\Bundle\ProductBundle\Service\Routing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RoutingLoader extends Loader
{
    use PrefixTrait;

    private const DIRECTORY = '@EkynaProductBundle/Resources/config/routing/front';

    private array $routingPrefix;
    private array $config;
    private bool  $loaded = false;

    public function __construct(array $config, array $routingPrefix, string $env = null) // TODO Features
    {
        parent::__construct($env);

        $this->config = array_replace([
            'catalog' => false,
        ], $config);
        $this->routingPrefix = $routingPrefix;
    }

    /**
     * @inheritDoc
     */
    public function load($resource, string $type = null)
    {
        if (true === $this->loaded) {
            throw new RuntimeException('Do not add the "product_routing" routes loader twice.');
        }

        $this->loaded = true;

        $collection = new RouteCollection();
        $accountCollection = new RouteCollection();

        $accountCollection->addCollection(
            $this->import(self::DIRECTORY . '/account.yaml', 'yaml')
        );

        if ($this->config['catalog']) {
            $routes = $this->import(self::DIRECTORY . '/account/catalog.yaml', 'yaml');
            $this->addPrefixes($routes, [
                'en' => '/catalogs',
                'fr' => '/catalogues',
                'es' => '/catalogos',
            ]);
            $accountCollection->addCollection($routes);
        }

        $this->addPrefixes($accountCollection, $this->routingPrefix);

        $collection->addCollection($accountCollection);

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, string $type = null): bool
    {
        return 'product_routing' === $type;
    }
}
