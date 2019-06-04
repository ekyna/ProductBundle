<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class AttributeChoice
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\AttributeChoiceTranslationInterface translate($locale = null, $create = false)
 */
class AttributeChoice extends RM\AbstractTranslatable implements Model\AttributeChoiceInterface
{
    use MediaSubjectTrait,
        RM\SortableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\AttributeInterface
     */
    protected $attribute;

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
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @inheritdoc
     */
    public function setAttribute(Model\AttributeInterface $attribute = null)
    {
        if ($attribute !== $this->attribute) {
            if ($previous = $this->attribute) {
                $this->attribute = null;
                $previous->removeChoice($this);
            }

            if ($this->attribute = $attribute) {
                $attribute->addChoice($this);
            }
        }

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
    public function setTitle(string $title)
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return AttributeChoiceTranslation::class;
    }
}
