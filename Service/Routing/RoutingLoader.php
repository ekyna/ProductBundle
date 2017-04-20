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

    private bool  $loaded = false;
    private array $config;

    public function __construct(array $config, string $env = null) // TODO Features
    {
        parent::__construct($env);

        $this->config = array_replace([
            'catalog' => false,
        ], $config);
    }

    /**
     * @inheritDoc
     */
    public function load($resource, string $type = null)
    {
        if (true === $this->loaded) {
            throw new RuntimeException('Do not add the "product account" routes loader twice.');
        }

        $this->loaded = true;

        $collection = new RouteCollection();
        $accountCollection = new RouteCollection();

        if ($this->config['catalog']) {
            $routes = $this->import(self::DIRECTORY . '/account/catalog.yaml', 'yaml');
            $this->addPrefixes($routes, [
                'en' => '/catalogs',
                'fr' => '/catalogues',
                'es' => '/catalogos',
            ]);
            $accountCollection->addCollection($routes);
        }

        if (0 < $accountCollection->count()) {
            // Should be configurable (sync with CMS)
            $this->addPrefixes($accountCollection, [
                'en' => '/my-account',
                'fr' => '/mon-compte',
                'es' => '/mi-cuenta',
            ]);

            $collection->addCollection($accountCollection);
        }

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
