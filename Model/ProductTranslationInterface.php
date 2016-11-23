<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model as RM;

/**
 * Interface ProductTranslationInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductTranslationInterface extends RM\TranslationInterface
{
    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return $this|ProductTranslationInterface
     */
    public function setTitle($title);

    /**
     * Returns the attributes (auto-generated) title.
     *
     * @return string
     */
    public function getAttributesTitle();

    /**
     * Sets the attributes (auto-generated) title.
     *
     * @param string $attributesTitle
     *
     * @return $this|ProductTranslationInterface
     */
    public function setAttributesTitle($attributesTitle);

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|ProductTranslationInterface
     */
    public function setDescription($description);
}
