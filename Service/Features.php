<?php

namespace Ekyna\Bundle\ProductBundle\Service;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;

/**
 * Class Features
 * @package Ekyna\Bundle\ProductBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Features
{
    public const COMPONENT     = 'component';
    public const COMPATIBILITY = 'compatibility'; // TODO

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
            self::COMPONENT => false,
        ], $config);
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

        return $this->config[$feature];
    }
}
