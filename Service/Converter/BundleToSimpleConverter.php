<?php

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Ekyna\Bundle\ProductBundle\Form\Type\Convert\BundleToSimpleType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class BundleToSimpleConverter
 * @package Ekyna\Bundle\ProductBundle\Service\Converter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleToSimpleConverter extends AbstractConverter
{
    /**
     * @var StockSubjectUpdaterInterface
     */
    private $stockSubjectUpdater;


    /**
     * Sets the stockSubjectUpdater.
     *
     * @param StockSubjectUpdaterInterface $stockSubjectUpdater
     */
    public function setStockSubjectUpdater(StockSubjectUpdaterInterface $stockSubjectUpdater): void
    {
        $this->stockSubjectUpdater = $stockSubjectUpdater;
    }

    /**
     * @inheritDoc
     */
    public function supportsSourceType(string $type): bool
    {
        return $type === ProductTypes::TYPE_BUNDLE;
    }

    /**
     * @inheritDoc
     */
    public function supportsTargetType(string $type): bool
    {
        return $type === ProductTypes::TYPE_SIMPLE;
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        return $this->source;
    }

    /**
     * @inheritDoc
     */
    protected function buildForm(): FormInterface
    {
        return $this->formFactory->create(BundleToSimpleType::class);
    }

    /**
     * @inheritDoc
     */
    protected function onConvert(): void
    {
        $this->stockSubjectUpdater->reset($this->target);

        $this->target
            ->setType(ProductTypes::TYPE_SIMPLE)
            ->getBundleSlots()->clear();
    }
}
