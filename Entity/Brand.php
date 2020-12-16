<?php

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

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New brand';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->translate()->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function setTitle(string $title)
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->translate()->getDescription();
    }

    /**
     * @inheritdoc
     */
    public function setDescription(string $description)
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSlug()
    {
        return $this->translate()->getSlug();
    }

    /**
     * @inheritdoc
     */
    public function setSlug(string $slug)
    {
        $this->translate()->setSlug($slug);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass(): string
    {
        return BrandTranslation::class;
    }

    /**
     * @inheritdoc
     */
    public static function getEntityTagPrefix()
    {
        return 'ekyna_product.brand';
    }
}
