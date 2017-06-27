<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Updater\BundleUpdater;
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
    private $persistenceHelper;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var BundleUpdater
     */
    private $bundleUpdater;


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
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $changed = $this->getBundleUpdater()->updateStock($bundle);

        return $changed || $this->ensureDisabledStockMode($bundle);
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        return $this->ensureDisabledStockMode($bundle);
    }

    /**
     * @inheritdoc
     */
    public function handleChildDataChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $netPrice = $this->priceCalculator->calculateBundleTotalPrice($bundle);

        if ($netPrice !== $bundle->getNetPrice()) {
            $bundle->setNetPrice($netPrice);

            return true;
        }

        // TODO weight ?

        return false;
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
