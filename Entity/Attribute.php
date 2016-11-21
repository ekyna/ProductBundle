<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class Attribute
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\AttributeTranslationInterface translate($locale = null, $create = false)
 */
class Attribute extends AbstractTranslatable implements Model\AttributeInterface
{
    use MediaSubjectTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\AttributeGroupInterface
     */
    protected $group;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $color;


    /**
     * @inheritdoc
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
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function setGroup(Model\AttributeGroupInterface $group)
    {
        $this->group = $group;
        $group->addAttribute($this);

        return $this;
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
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @inheritdoc
     */
    public function setColor($color)
    {
        $this->color = $color;

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
    protected function getTranslationClass()
    {
        return AttributeTranslation::class;
    }
}
