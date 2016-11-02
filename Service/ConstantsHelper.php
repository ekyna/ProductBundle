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
}
