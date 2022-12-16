<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Updater\ConfigurableUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class ConfigurableHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableHandler extends AbstractHandler
{
    private ?ConfigurableUpdater $configurableUpdater = null;

    public function __construct(
        private readonly PriceCalculator              $priceCalculator,
        private readonly StockSubjectUpdaterInterface $stockUpdater
    ) {
    }

    public function handleInsert(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        $changed = $this->stockUpdater->update($bundle);

        return $this->getConfigurableUpdater()->updateMinPrice($bundle) || $changed;
    }

    public function handleUpdate(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        $changed = $this->stockUpdater->update($bundle);

        return $this->getConfigurableUpdater()->updateMinPrice($bundle) || $changed;
    }

    public function handleChildPriceChange(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        return $this->getConfigurableUpdater()->updateMinPrice($bundle);
    }

    public function handleChildStockChange(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        return $this->stockUpdater->update($bundle);
    }

    public function supports(ProductInterface $product): bool
    {
        return $product->getType() === ProductTypes::TYPE_CONFIGURABLE;
    }

    protected function getConfigurableUpdater(): ConfigurableUpdater
    {
        if (null !== $this->configurableUpdater) {
            return $this->configurableUpdater;
        }

        return $this->configurableUpdater = new ConfigurableUpdater($this->priceCalculator);
    }
}
