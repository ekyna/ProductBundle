<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Updater\ConfigurableUpdater;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ConfigurableHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableHandler extends AbstractHandler
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var ConfigurableUpdater
     */
    private $configurableUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param PriceCalculator            $priceCalculator
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        PriceCalculator $priceCalculator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * @inheritDoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        $updater = $this->getConfigurableUpdater();

        $changed = $updater->updateStock($bundle);

        $changed |= $updater->updateAvailability($bundle);

        $changed |= $updater->updatePrice($bundle);

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        $updater = $this->getConfigurableUpdater();

        // TODO remove : stock should only change from children
        $changed = $updater->updateStock($bundle);
        $changed |= $updater->updateAvailability($bundle);
        $changed |= $updater->updatePrice($bundle);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildPriceChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        return $this->getConfigurableUpdater()->updatePrice($bundle);
    }

    /**
     * @inheritDoc
     */
    public function handleChildAvailabilityChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        return $this->getConfigurableUpdater()->updateAvailability($bundle);
    }

    /**
     * @inheritDoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        return $this->getConfigurableUpdater()->updateStock($bundle);
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_CONFIGURABLE;
    }

    /**
     * Returns the configurable updater.
     *
     * @return ConfigurableUpdater
     */
    protected function getConfigurableUpdater()
    {
        if (null !== $this->configurableUpdater) {
            return $this->configurableUpdater;
        }

        return $this->configurableUpdater = new ConfigurableUpdater($this->priceCalculator);
    }
}
