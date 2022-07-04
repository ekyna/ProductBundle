<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ResourceBundle\Helper\AbstractConstantsHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ConstantsHelper
 * @package Ekyna\Bundle\ProductBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConstantsHelper extends AbstractConstantsHelper
{
    private AttributeTypeRegistryInterface $attributeTypeRegistry;

    public function __construct(TranslatorInterface $translator, AttributeTypeRegistryInterface $attributeTypeRegistry)
    {
        parent::__construct($translator);

        $this->attributeTypeRegistry = $attributeTypeRegistry;
    }

    /**
     * Renders the bundle rule type label.
     *
     * @param Model\BundleRuleInterface|string $typeOrRule
     */
    public function renderBundleRuleTypeLabel($typeOrRule): string
    {
        if ($typeOrRule instanceof Model\BundleRuleInterface) {
            $typeOrRule = $typeOrRule->getType();
        }

        if (Model\BundleRuleTypes::isValid($typeOrRule)) {
            return $this->renderLabel(Model\BundleRuleTypes::getLabel($typeOrRule));
        }

        return $this->renderLabel(null);
    }

    /**
     * Renders the product type label.
     *
     * @param Model\ProductInterface|string $typeOrProduct
     */
    public function renderProductTypeLabel($typeOrProduct): string
    {
        if ($typeOrProduct instanceof Model\ProductInterface) {
            $typeOrProduct = $typeOrProduct->getType();
        }

        if (Model\ProductTypes::isValid($typeOrProduct)) {
            return $this->renderLabel(Model\ProductTypes::getLabel($typeOrProduct));
        }

        return $this->renderLabel(null);
    }

    /**
     * Renders the product type badge.
     *
     * @param Model\ProductInterface|string $typeOrProduct
     */
    public function renderProductTypeBadge($typeOrProduct, bool $long = true): string
    {
        if ($typeOrProduct instanceof Model\ProductInterface) {
            $typeOrProduct = $typeOrProduct->getType();
        }

        $theme = 'default';
        if (Model\ProductTypes::isValid($typeOrProduct)) {
            $theme = Model\ProductTypes::getTheme($typeOrProduct);
        }

        $label = $this->renderProductTypeLabel($typeOrProduct);
        if (!$long) {
            $label = strtoupper($label[0]);
        }

        return $this->renderBadge($label, $theme, ['product-type-badge']);
    }

    /**
     * Renders the product reference type label.
     *
     * @param Model\ProductReferenceInterface|string $typeOrReference
     */
    public function renderProductReferenceTypeLabel($typeOrReference): string
    {
        if ($typeOrReference instanceof Model\ProductReferenceInterface) {
            $typeOrReference = $typeOrReference->getType();
        }

        return $this->renderLabel(Model\ProductReferenceTypes::getLabel($typeOrReference));
    }

    /**
     * Renders the attribute type label.
     *
     * @param string|Model\AttributeInterface $typeOrAttribute
     */
    public function renderAttributeTypeLabel($typeOrAttribute): string
    {
        if ($typeOrAttribute instanceof Model\AttributeInterface) {
            $typeOrAttribute = $typeOrAttribute->getType();
        }

        $type = $this->attributeTypeRegistry->getType($typeOrAttribute);

        return $type->getLabel()->trans($this->translator);
    }

    /**
     * Renders the product best-seller label.
     *
     * @param Model\ProductInterface|string $modeOrProduct
     */
    public function renderProductBestSellerLabel($modeOrProduct): string
    {
        if ($modeOrProduct instanceof Model\ProductInterface) {
            $modeOrProduct = $modeOrProduct->getBestSeller();
        }

        return $this->renderHighlightModeLabel($modeOrProduct);
    }

    /**
     * Renders the product best-seller badge.
     *
     * @param Model\ProductInterface|string $modeOrProduct
     */
    public function renderProductBestSellerBadge($modeOrProduct): string
    {
        if ($modeOrProduct instanceof Model\ProductInterface) {
            $modeOrProduct = $modeOrProduct->getBestSeller();
        }

        return $this->renderHighlightModeBadge($modeOrProduct);
    }

    /**
     * Renders the product cross-selling label.
     *
     * @param Model\ProductInterface|string $modeOrProduct
     */
    public function renderProductCrossSellingLabel($modeOrProduct): string
    {
        if ($modeOrProduct instanceof Model\ProductInterface) {
            $modeOrProduct = $modeOrProduct->getCrossSelling();
        }

        return $this->renderHighlightModeLabel($modeOrProduct);
    }

    /**
     * Renders the product cross-selling badge.
     *
     * @param Model\ProductInterface|string $modeOrProduct
     */
    public function renderProductCrossSellingBadge($modeOrProduct): string
    {
        if ($modeOrProduct instanceof Model\ProductInterface) {
            $modeOrProduct = $modeOrProduct->getCrossSelling();
        }

        return $this->renderHighlightModeBadge($modeOrProduct);
    }

    /**
     * Renders the highlight mode label.
     */
    protected function renderHighlightModeLabel(string $mode): string
    {
        if (Model\HighlightModes::isValid($mode)) {
            return $this->renderLabel(Model\HighlightModes::getLabel($mode));
        }

        return $this->renderLabel(null);
    }

    /**
     * Renders the highlight mode badge.
     */
    protected function renderHighlightModeBadge(string $mode): string
    {
        $theme = 'default';
        if (Model\HighlightModes::isValid($mode)) {
            $theme = Model\HighlightModes::getTheme($mode);
        }

        $label = $this->renderHighlightModeLabel($mode);

        return $this->renderBadge($label, $theme);
    }
}
