<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\CategoryTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class CategoryTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryTranslation extends AbstractTranslation implements CategoryTranslationInterface
{
    protected ?string $title       = null;
    protected ?string $description = null;
    protected ?string $slug        = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): CategoryTranslationInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): CategoryTranslationInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): CategoryTranslationInterface
    {
        $this->slug = $slug;

        return $this;
    }
}
