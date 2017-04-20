<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Exception\LogicException;

use function array_replace_recursive;
use function array_shift;
use function count;
use function explode;

/**
 * Class Features
 * @package Ekyna\Bundle\ProductBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Features
{
    public const COMPONENT        = 'component';
    public const GTIN13_GENERATOR = 'gtin13_generator';
    //public const COMPATIBILITY  = 'compatibility'; // TODO
    //public const CROSS_SELLING  = 'compatibility'; // TODO

    private const DEFAULTS = [
        self::COMPONENT        => [
            'enabled' => false,
        ],
        self::GTIN13_GENERATOR => [
            'enabled'      => false,
            'manufacturer' => null,
        ],
    ];

    private array $config;

    public function __construct(array $config)
    {
        // Must be kept in sync with:
        /** @see \Ekyna\Bundle\ProductBundle\DependencyInjection\Configuration::addFeatureSection */
        $this->config = array_replace_recursive(self::DEFAULTS, $config);
    }

    /**
     * Returns whether the given feature is enabled.
     *
     * @param string $feature
     *
     * @return bool
     */
    public function isEnabled(string $feature): bool
    {
        if (!isset($this->config[$feature])) {
            throw new UnexpectedValueException("Unknown feature '$feature'.");
        }

        return $this->config[$feature]['enabled'];
    }

    /**
     * Returns the feature configuration.
     *
     * @param string $feature
     *
     * @return mixed
     */
    public function getConfig(string $feature)
    {
        $paths = explode('.', $feature);

        if (1 === count($paths)) {
            $paths[] = 'enabled';
        }

        $config = $this->config;

        while ($path = array_shift($paths)) {
            if (!isset($config[$path])) {
                throw new LogicException("Unknown feature '$feature'.");
            }

            $config = $config[$path];
        }

        if (!isset($config)) {
            throw new LogicException("Unexpected feature '$feature'.");
        }

        return $config;
    }
}
