<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CoreBundle\Model\TreeInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Resource\Model as RM;
use Ekyna\Bundle\CmsBundle\Model as Cms;

/**
 * Interface CategoryInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CategoryTranslationInterface translate($locale = null, $create = false)
 */
interface CategoryInterface extends
    VisibilityInterface,
    Cms\ContentSubjectInterface,
    Cms\SeoSubjectInterface,
    MediaSubjectInterface,
    TreeInterface,
    RM\TimestampableInterface,
    RM\TranslatableInterface,
    RM\TaggedEntityInterface
{
    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|CategoryInterface
     */
    public function setName($name);

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns whether or not the category has the given child.
     *
     * @param CategoryInterface $child
     *
     * @return bool
     */
    public function hasChild(CategoryInterface $child);

    /**
     * Adds the child category.
     *
     * @param CategoryInterface $child
     *
     * @return $this|CategoryInterface
     */
    public function addChild(CategoryInterface $child);

    /**
     * Removes the child category.
     *
     * @param CategoryInterface $child
     *
     * @return $this|CategoryInterface
     */
    public function removeChild(CategoryInterface $child);

    /**
     * Returns the children categories.
     *
     * @return ArrayCollection|CategoryInterface[]
     */
    public function getChildren();

    /**
     * Sets the parent category.
     *
     * @param CategoryInterface $parent
     *
     * @return $this|CategoryInterface
     */
    public function setParent(CategoryInterface $parent = null);

    /**
     * Returns the parent category.
     *
     * @return CategoryInterface
     */
    public function getParent();

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the (translated) title.
     *
     * @param string $title
     *
     * @return $this|CategoryInterface
     */
    public function setTitle(string $title);

    /**
     * Returns the (translated) description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the (translated) description.
     *
     * @param string $description
     *
     * @return $this|CategoryInterface
     */
    public function setDescription(string $description);

    /**
     * Returns the (translated) slug.
     *
     * @return string
     */
    public function getSlug();

    /**
     * Sets the (translated) slug.
     *
     * @param string $slug
     *
     * @return $this|CategoryInterface
     */
    public function setSlug(string $slug);
}
