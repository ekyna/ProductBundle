<?php

declare(strict_types=1);

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
    public function getTitle(): ?string;

    public function setTitle(?string $title): CategoryTranslationInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): CategoryTranslationInterface;

    /**
     * Returns the slug (auto-generated).
     */
    public function getSlug(): ?string;

    /**
     * Sets the slug (auto-generated).
     */
    public function setSlug(?string $slug): CategoryTranslationInterface;
}
