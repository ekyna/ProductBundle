<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Copier\CopyInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class ProductTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method ProductInterface getTranslatable()
 */
class ProductTranslation extends AbstractTranslation implements ProductTranslationInterface, CopyInterface
{
    protected ?string $title           = null;
    protected ?string $subTitle        = null;
    protected ?string $attributesTitle = null;
    protected ?string $description     = null;
    protected ?string $slug            = null;

    public function onCopy(CopierInterface $copier): void
    {
        $this->title = null;
        $this->slug = null;
        $this->attributesTitle = null;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): ProductTranslationInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): ProductTranslationInterface
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    public function getAttributesTitle(): ?string
    {
        return $this->attributesTitle;
    }

    public function setAttributesTitle(?string $attributesTitle): ProductTranslationInterface
    {
        $this->attributesTitle = $attributesTitle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): ProductTranslationInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): ProductTranslationInterface
    {
        $this->slug = $slug;

        return $this;
    }

    public function clear(): ProductTranslationInterface
    {
        $this->title = null;
        $this->attributesTitle = null;
        $this->description = null;
        $this->slug = null;

        return $this;
    }
}
