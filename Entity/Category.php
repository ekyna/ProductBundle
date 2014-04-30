<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\CmsBundle\Model\ContentSubjectInterface;
use Ekyna\Bundle\CmsBundle\Model\ContentSubjectTrait;
use Ekyna\Bundle\CmsBundle\Entity\Seo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Category.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Category implements ContentSubjectInterface
{
    use ContentSubjectTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $left;

    /**
     * @var integer
     */
    protected $right;

    /**
     * @var integer
     */
    protected $root;

    /**
     * @var integer
     */
    protected $level;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var CategoryImage
     */
    protected $image;

    /**
     * @var \Ekyna\Bundle\CmsBundle\Entity\Seo
     */
    protected $seo;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $children;

    /**
     * @var Category
     */
    protected $parent;

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
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->seo = new Seo();
    }

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set left
     *
     * @param integer $left
     * @return Category
     */
    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    /**
     * Get left
     *
     * @return integer 
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set right
     *
     * @param integer $right
     * @return Category
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * Get right
     *
     * @return integer 
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set root
     *
     * @param integer $root
     * @return Category
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return Category
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Category
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set image
     *
     * @param CategoryImage $image
     * @return Category
     */
    public function setImage(CategoryImage $image = null)
    {
        $image->setCategory($this);
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return CategoryImage 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set seo
     *
     * @param \Ekyna\Bundle\CmsBundle\Entity\Seo $seo
     * @return Category
     */
    public function setSeo(Seo $seo = null)
    {
        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo
     *
     * @return \Ekyna\Bundle\CmsBundle\Entity\Seo 
     */
    public function getSeo()
    {
        return $this->seo;
    }

    /**
     * Add children
     *
     * @param Category $children
     * @return Category
     */
    public function addChild(Category $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Category $children
     */
    public function removeChild(Category $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return ArrayCollection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param Category $parent
     * @return Category
     */
    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Category 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set created at
     *
     * @param \DateTime $createdAt
     * @return Category
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updated at
     *
     * @param \DateTime $updatedAt
     * @return Category
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updated at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deleted at
     *
     * @param \DateTime $deletedAt
     * @return Category
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deleted at
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}
