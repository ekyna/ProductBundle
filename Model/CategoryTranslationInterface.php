<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface CategoryTranslationInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CategoryInterface getTranslatable()
 */
interface CategoryTranslationInterface extends TranslationInterface
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
     * @return $this|CategoryTranslationInterface
     */
    public function setTitle($title);

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
     * @return $this|CategoryTranslationInterface
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
     * @return $this|CategoryTranslationInterface
     */
    public function setSlug($slug);
}
