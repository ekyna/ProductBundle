<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariantUpdater;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class VariantHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantHandler extends AbstractHandler
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
     * @var VariableUpdater
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
     * @inheritDoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        $changed = false;

        // Generate attributes designation and title if needed
        if ($this->variantUpdater->updateAttributesDesignationAndTitle($variant)) {
            $changed = true;
        }

        // Set tax group regarding to parent/variable if needed
        if ($this->variantUpdater->updateTaxGroup($variant)) {
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        if (null === $variable = $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }

        $changed = false;

        // Generate attributes designation and title if needed
        if ($this->variantUpdater->updateAttributesDesignationAndTitle($variant)) {
            $changed = true;
        }

        // Update parent/variable minimum price if variant price has changed
        if ($this->persistenceHelper->isChanged($variant, 'netPrice')) {
            if ($this->variableUpdater->updateMinPrice($variable)) {
                $this->persistenceHelper->persistAndRecompute($variable, true);
            }
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIANT;
    }
}
