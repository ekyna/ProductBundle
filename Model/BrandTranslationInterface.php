<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface BrandTranslationInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method BrandInterface getTranslatable()
 */
interface BrandTranslationInterface extends TranslationInterface
{
    public function getTitle(): ?string;

    public function setTitle(?string $title): BrandTranslationInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): BrandTranslationInterface;

    /**
     * Returns the slug (auto-generated).
     */
    public function getSlug(): ?string;

    /**
     * Sets the slug (auto-generated).
     */
    public function setSlug(?string $slug): BrandTranslationInterface;
}
