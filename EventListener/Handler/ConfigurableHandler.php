<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Updater\ConfigurableUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
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
     * @var PriceInvalidator
     */
    private $priceInvalidator;

    /**
     * @var StockSubjectUpdaterInterface
     */
    private $stockUpdater;

    /**
     * @var ConfigurableUpdater
     */
    private $configurableUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface   $persistenceHelper
     * @param PriceCalculator              $priceCalculator
     * @param PriceInvalidator             $priceInvalidator
     * @param StockSubjectUpdaterInterface $stockUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        PriceCalculator $priceCalculator,
        PriceInvalidator $priceInvalidator,
        StockSubjectUpdaterInterface $stockUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->priceCalculator = $priceCalculator;
        $this->priceInvalidator = $priceInvalidator;
        $this->stockUpdater = $stockUpdater;
    }

    /**
     * @inheritDoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        $updater = $this->getConfigurableUpdater();

        $changed = $this->stockUpdater->update($bundle);

        $changed |= $updater->updateAvailability($bundle);

        $changed |= $updater->updateMinPrice($bundle);

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        $updater = $this->getConfigurableUpdater();

        $changed = $this->stockUpdater->update($bundle);

        $changed |= $updater->updateAvailability($bundle);

        $changed |= $updater->updateMinPrice($bundle);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildPriceChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_CONFIGURABLE);

        $this->priceInvalidator->invalidateByProduct($bundle);

        return $this->getConfigurableUpdater()->updateMinPrice($bundle);
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

        return $this->stockUpdater->update($bundle);
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
