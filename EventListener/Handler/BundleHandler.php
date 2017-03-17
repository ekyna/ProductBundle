<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Updater\BundleUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class BundleHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleHandler extends AbstractHandler
{
    /**
     * @var PersistenceHelperInterface
     */
    //private $persistenceHelper;

    /**
     * @var StockSubjectUpdaterInterface
     */
    //private $stockUpdater;

    /**
     * @var BundleUpdater
     */
    private $bundleUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     * @param StockSubjectUpdaterInterface     $stockUpdater
     */
    /*public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockSubjectUpdaterInterface $stockUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUpdater = $stockUpdater;

        $this->bundleUpdater = new BundleUpdater();
    }*/

    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        //$this->stockUpdater->update($bundle);

        return $this->ensureDisabledStockMode($bundle);
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        /*if ($this->persistenceHelper->isChanged($bundle, ['inStock', 'virtualStock', 'estimatedDateOfArrival'])) {
            return $this->stockUpdater->updateStockState($bundle);
        }*/

        return $this->ensureDisabledStockMode($bundle);
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        return $this->getBundleUpdater()->updateStock($bundle);
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_BUNDLE;
    }

    /**
     * Returns the bundle updater.
     *
     * @return BundleUpdater
     */
    protected function getBundleUpdater()
    {
        if (null !== $this->bundleUpdater) {
            return $this->bundleUpdater;
        }

        return $this->bundleUpdater = new BundleUpdater();
    }
}
