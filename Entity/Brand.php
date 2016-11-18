<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Resource\Model as ResourceModel;
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
class Brand extends ResourceModel\AbstractTranslatable implements Model\BrandInterface
{
    use Cms\ContentSubjectTrait,
        Cms\SeoSubjectTrait,
        MediaSubjectTrait,
        ResourceModel\SortableTrait,
        ResourceModel\TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;


    /**
     * Returns the string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getId()
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
    public function getDescription()
    {
        return $this->translate()->getDescription();
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return BrandTranslation::class;
    }
}
