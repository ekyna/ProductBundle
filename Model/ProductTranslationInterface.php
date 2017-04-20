<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model as RM;

/**
 * Interface ProductTranslationInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method ProductInterface getTranslatable()
 */
interface ProductTranslationInterface extends RM\TranslationInterface
{
    public function getTitle(): ?string;

    public function setTitle(?string $title): ProductTranslationInterface;

    public function getSubTitle(): ?string;

    public function setSubTitle(?string $subTitle): ProductTranslationInterface;

    /**
     * Returns the attributes (auto-generated) title.
     */
    public function getAttributesTitle(): ?string;

    /**
     * Sets the attributes (auto-generated) title.
     */
    public function setAttributesTitle(?string $attributesTitle): ProductTranslationInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): ProductTranslationInterface;

    /**
     * Returns the slug (auto-generated).
     */
    public function getSlug(): ?string;

    /**
     * Sets the slug (auto-generated).
     */
    public function setSlug(?string $slug): ProductTranslationInterface;

    /**
     * Clears the translation data.
     */
    public function clear(): ProductTranslationInterface;
}
