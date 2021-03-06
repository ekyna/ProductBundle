<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class ProductTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method ProductInterface getTranslatable()
 */
class ProductTranslation extends AbstractTranslation implements ProductTranslationInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $subTitle;

    /**
     * @var string
     */
    protected $attributesTitle;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $slug;


    /**
     * @inheritDoc
     */
    public function __clone()
    {
        parent::__clone();

        $this->title = null;
        $this->slug = null;
        $this->attributesTitle = null;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * @inheritdoc
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributesTitle()
    {
        return $this->attributesTitle;
    }

    /**
     * @inheritdoc
     */
    public function setAttributesTitle($attributesTitle)
    {
        $this->attributesTitle = $attributesTitle;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @inheritdoc
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->title = null;
        $this->attributesTitle = null;
        $this->description = null;
        $this->slug = null;

        return $this;
    }
}
