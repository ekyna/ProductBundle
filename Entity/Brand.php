<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Resource\Model as RM;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class Brand
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\BrandTranslationInterface translate($locale = null, $create = false)
 */
class Brand extends RM\AbstractTranslatable implements Model\BrandInterface
{
    use Cms\ContentSubjectTrait;
    use Cms\SeoSubjectTrait;
    use MediaSubjectTrait;
    use Model\VisibilityTrait;
    use RM\SortableTrait;
    use RM\TaggedEntityTrait;
    use RM\TimestampableTrait;

    protected ?int $id = null;
    protected ?string $name = null;

    public function __toString(): string
    {
        return $this->name ?: 'New brand';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Model\BrandInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): Model\BrandInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->translate()->getDescription();
    }

    public function setDescription(?string $description): Model\BrandInterface
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->translate()->getSlug();
    }

    public function setSlug(?string $slug): Model\BrandInterface
    {
        $this->translate()->setSlug($slug);

        return $this;
    }

    protected function getTranslationClass(): string
    {
        return BrandTranslation::class;
    }

    public static function getEntityTagPrefix(): string
    {
        return 'ekyna_product.brand';
    }
}
