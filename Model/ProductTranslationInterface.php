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
     * Returns the subTitle.
     *
     * @return string
     */
    public function getSubTitle();

    /**
     * Sets the subTitle.
     *
     * @param string $subTitle
     *
     * @return $this|ProductTranslationInterface
     */
    public function setSubTitle($subTitle);

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

    /**
     * Returns the slug (auto-generated).
     *
     * @return string
     */
    public function getSlug();

    /**
     * Sets the slug (auto-generated).
     *
     * @param string $slug
     *
     * @return $this|ProductTranslationInterface
     */
    public function setSlug($slug);

    /**
     * Clears the translation data.
     *
     * @return $this|ProductTranslationInterface
     */
    public function clear();
}
