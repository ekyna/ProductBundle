<?php

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;

/**
 * Interface TypeInterface
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TypeInterface
{
    /**
     * Renders the product attribute.
     *
     * @param ProductAttributeInterface $productAttribute
     * @param string                    $locale
     *
     * @return string
     */
    public function render(ProductAttributeInterface $productAttribute, $locale = null);

    /**
     * Returns whether or not this type works with attribute choices.
     *
     * @return bool
     */
    public function hasChoices();

    /**
     * Returns the validation constraints.
     *
     * @param ProductAttributeInterface $productAttribute
     *
     * @return array
     */
    public function getConstraints(ProductAttributeInterface $productAttribute);

    /**
     * Returns the config show fields.
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    public function getConfigShowFields(AttributeInterface $attribute);

    /**
     * Returns configuration defaults.
     *
     * @return array
     */
    public function getConfigDefaults();

    /**
     * Returns the configuration form type class.
     *
     * @return string
     */
    public function getConfigType();

    /**
     * Returns the product attribute form type class.
     *
     * @return string
     */
    public function getFormType();

    /**
     * Returns the label.
     *
     * @return string
     */
    public function getLabel();
}