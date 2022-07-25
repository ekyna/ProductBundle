<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariantUpdater;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractVariantHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractVariantHandler extends AbstractHandler
{
    private ?VariantUpdater  $variantUpdater  = null;
    private ?VariableUpdater $variableUpdater = null;

    public function __construct(
        protected readonly PersistenceHelperInterface     $persistenceHelper,
        protected readonly LocaleProviderInterface        $localeProvider,
        protected readonly PriceCalculator                $priceCalculator,
        protected readonly AttributeTypeRegistryInterface $typeRegistry,
        protected readonly ProductRepositoryInterface     $productRepository
    ) {
    }

    protected function getVariantUpdater(): VariantUpdater
    {
        if (null !== $this->variantUpdater) {
            return $this->variantUpdater;
        }

        return $this->variantUpdater = new VariantUpdater(
            $this->persistenceHelper,
            $this->localeProvider,
            $this->typeRegistry
        );
    }

    protected function getVariableUpdater(): VariableUpdater
    {
        if (null !== $this->variableUpdater) {
            return $this->variableUpdater;
        }

        return $this->variableUpdater = new VariableUpdater($this->priceCalculator);
    }
}
