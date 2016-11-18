<?php

namespace Ekyna\Bundle\ProductBundle\Service;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ResourceBundle\Helper\AbstractConstantsHelper;

/**
 * Class ConstantsHelper
 * @package Ekyna\Bundle\ProductBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConstantsHelper extends AbstractConstantsHelper
{
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
}
