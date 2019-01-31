<?php

namespace Ekyna\Bundle\ProductBundle\Service;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ResourceBundle\Helper\AbstractConstantsHelper;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ConstantsHelper
 * @package Ekyna\Bundle\ProductBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConstantsHelper extends AbstractConstantsHelper
{
    /**
     * @var AttributeTypeRegistryInterface
     */
    private $attributeTypeRegistry;


    /**
     * @inheritDoc
     */
    public function __construct(TranslatorInterface $translator, AttributeTypeRegistryInterface $attributeTypeRegistry)
    {
        parent::__construct($translator);

        $this->attributeTypeRegistry = $attributeTypeRegistry;
    }

    /**
     * Renders the product type label.
     *
     * @param Model\ProductInterface|string $typeOrProduct
     *
     * @return string
     */
    public function renderProductTypeLabel($typeOrProduct)
    {
        if ($typeOrProduct instanceof Model\ProductInterface) {
            $typeOrProduct = $typeOrProduct->getType();
        }

        if (Model\ProductTypes::isValid($typeOrProduct)) {
            return $this->renderLabel(Model\ProductTypes::getLabel($typeOrProduct));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the product type badge.
     *
     * @param Model\ProductInterface|string $typeOrProduct
     * @param bool                          $long
     *
     * @return string
     */
    public function renderProductTypeBadge($typeOrProduct, $long = true)
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

        return $this->renderBadge($label, $theme);
    }

    /**
     * Renders the product reference type label.
     *
     * @param Model\ProductReferenceInterface|string $typeOrReference
     *
     * @return string
     */
    public function renderProductReferenceTypeLabel($typeOrReference)
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
     *
     * @return string
     */
    public function renderAttributeTypeLabel($typeOrAttribute)
    {
        if ($typeOrAttribute instanceof Model\AttributeInterface) {
            $typeOrAttribute = $typeOrAttribute->getType();
        }

        $type = $this->attributeTypeRegistry->getType($typeOrAttribute);

        return $this->renderLabel($type->getLabel());
    }

    /**
     * Renders the product best seller label.
     *
     * @param Model\ProductInterface|string $modeOrProduct
     *
     * @return string
     */
    public function renderProductBestSellerLabel($modeOrProduct)
    {
        if ($modeOrProduct instanceof Model\ProductInterface) {
            $modeOrProduct = $modeOrProduct->getBestSeller();
        }

        return $this->renderHighlightModeLabel($modeOrProduct);
    }

    /**
     * Renders the product best seller badge.
     *
     * @param Model\ProductInterface|string $modeOrProduct
     *
     * @return string
     */
    public function renderProductBestSellerBadge($modeOrProduct)
    {
        if ($modeOrProduct instanceof Model\ProductInterface) {
            $modeOrProduct = $modeOrProduct->getBestSeller();
        }

        return $this->renderHighlightModeBadge($modeOrProduct);
    }

    /**
     * Renders the product cross selling label.
     *
     * @param Model\ProductInterface|string $modeOrProduct
     *
     * @return string
     */
    public function renderProductCrossSellingLabel($modeOrProduct)
    {
        if ($modeOrProduct instanceof Model\ProductInterface) {
            $modeOrProduct = $modeOrProduct->getCrossSelling();
        }

        return $this->renderHighlightModeLabel($modeOrProduct);
    }

    /**
     * Renders the product cross selling badge.
     *
     * @param Model\ProductInterface|string $modeOrProduct
     *
     * @return string
     */
    public function renderProductCrossSellingBadge($modeOrProduct)
    {
        if ($modeOrProduct instanceof Model\ProductInterface) {
            $modeOrProduct = $modeOrProduct->getCrossSelling();
        }

        return $this->renderHighlightModeBadge($modeOrProduct);
    }

    /**
     * Renders the highlight mode label.
     *
     * @param string $mode
     *
     * @return string
     */
    protected function renderHighlightModeLabel($mode)
    {
        if (Model\HighlightModes::isValid($mode)) {
            return $this->renderLabel(Model\HighlightModes::getLabel($mode));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the highlight mode badge.
     *
     * @param string $mode
     *
     * @return string
     */
    protected function renderHighlightModeBadge($mode)
    {
        $theme = 'default';
        if (Model\ProductTypes::isValid($mode)) {
            $theme = Model\ProductTypes::getTheme($mode);
        }

        $label = $this->renderHighlightModeLabel($mode);

        return $this->renderBadge($label, $theme);
    }
}
