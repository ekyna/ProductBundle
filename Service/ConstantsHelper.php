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
