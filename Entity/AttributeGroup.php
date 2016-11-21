<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model AS RM;

/**
 * Class AttributeGroup
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\AttributeGroupTranslationInterface translate($locale = null, $create = false)
 */
class AttributeGroup extends RM\AbstractTranslatable implements Model\AttributeGroupInterface
{
    use RM\SortableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection|Model\AttributeInterface[]
     */
    protected $attributes;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->attributes = new ArrayCollection();
    }

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
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function hasAttribute(Model\AttributeInterface $attribute)
    {
        return $this->attributes->contains($attribute);
    }

    /**
     * @inheritdoc
     */
    public function addAttribute(Model\AttributeInterface $attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            if ($attribute->getGroup() !== $this) {
                $attribute->setGroup($this);
            }
            $this->attributes->add($attribute);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttribute(Model\AttributeInterface $attribute)
    {
        if ($this->hasAttribute($attribute)) {
            $attribute->setGroup(null);
            $this->attributes->removeElement($attribute);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return AttributeGroupTranslation::class;
    }
}
