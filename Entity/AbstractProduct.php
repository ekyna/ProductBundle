<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Sale\Product\OptionInterface;
use Ekyna\Component\Sale\Product\ProductInterface;
use Ekyna\Component\Sale\Product\ProductTypes;

/**
 * AbstractProduct.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractProduct implements ProductInterface
{
    use \Ekyna\Component\Sale\PriceableTrait;
    use \Ekyna\Component\Sale\ReferenceableTrait;
    use \Ekyna\Component\Sale\WeighableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $type;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $options;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $deletedAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = ProductTypes::PHYSICAL;
        $this->options = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDesignation();
    }

    /**
     * Returns the identifier.
     * 
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the type
     * 
     * @param integer $type
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns wether the product has options or not.
     * 
     * @return boolean
     */
    public function hasOptions()
    {
        return 0 < $this->options->count();
    }

    /**
     * Returns wether the product has the given option or not.
     * 
     * @param \Ekyna\Component\Sale\Product\OptionInterface $option
     * 
     * @return boolean
     */
    public function hasOption(OptionInterface $option)
    {
        return $this->options->contains($option);
    }

    /**
     * Adds an option.
     *
     * @param \Ekyna\Component\Sale\Product\OptionInterface $option
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function addOption(OptionInterface $option)
    {
        $option->setProduct($this);
        $this->options->add($option);

        return $this;
    }

    /**
     * Removes an option.
     *
     * @param \Ekyna\Component\Sale\Product\OptionInterface $option
     */
    public function removeOption(OptionInterface $option)
    {
        $this->options->removeElement($option);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsGroups()
    {
        $groups = array();
        foreach ($this->options as $option) {
            if (! in_array($group = $option->getGroup(), $groups)) {
                $groups[] = $group;
            }
        }
        return $groups;
    }

    /**
     * Sets the category.
     *
     * @param \Ekyna\Bundle\ProductBundle\Entity\Category $category
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Returns the category.
     *
     * @return \Ekyna\Bundle\ProductBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Sets the slug.
     *
     * @param string $slug
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Returns the slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Sets the "created at" datetime.
     *
     * @param \DateTime $createdAt
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns the "created at" datetime
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the "updated at" datetime.
     *
     * @param \DateTime $updatedAt
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns the "updated at" datetime.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the "deleted at" datetime.
     *
     * @param \DateTime $deletedAt
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Returns the "deleted at" datetime.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}
