<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\CategoryInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class Category
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\CategoryTranslationInterface translate($locale = null, $create = false)
 */
class Category extends RM\AbstractTranslatable implements Model\CategoryInterface
{
    use Model\VisibilityTrait;
    use Cms\ContentSubjectTrait;
    use Cms\SeoSubjectTrait;
    use MediaSubjectTrait;
    use RM\TaggedEntityTrait;
    use RM\TimestampableTrait;
    use RM\TreeTrait;

    protected ?string $name = null;

    public function __construct()
    {
        parent::__construct();

        $this->initializeNode();
        $this->initializeTimestampable();
    }

    public function __toString(): string
    {
        return $this->name ?: 'New category';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): CategoryInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): CategoryInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->translate()->getDescription();
    }

    public function setDescription(?string $description): CategoryInterface
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->translate()->getSlug();
    }

    public function setSlug(?string $slug): CategoryInterface
    {
        $this->translate()->setSlug($slug);

        return $this;
    }

    protected function getTranslationClass(): string
    {
        return CategoryTranslation::class;
    }

    public static function getEntityTagPrefix(): string
    {
        return 'ekyna_product.category';
    }
}
