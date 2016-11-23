<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariantUpdater;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class VariableHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableHandler extends AbstractHandler
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var VariantUpdater
     */
    private $variantUpdater;

    /**
     * @var \Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater
     */
    private $variableUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param LocaleProviderInterface    $localeProvider
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        LocaleProviderInterface $localeProvider
    ) {
        $this->persistenceHelper = $persistenceHelper;

        $this->variantUpdater = new VariantUpdater($persistenceHelper, $localeProvider);
        $this->variableUpdater = new VariableUpdater();
    }

    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->variableUpdater->updateMinPrice($variable);
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        if ($this->persistenceHelper->isChanged($variable, 'taxGroup')) {
            foreach ($variable->getVariants() as $variant) {
                if ($this->variantUpdater->updateTaxGroup($variant)) {
                    $this->persistenceHelper->persistAndRecompute($variant, true);
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->variableUpdater->updateStockState($variable);
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIABLE;
    }
}
