<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class Catalog
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Catalog implements RM\ResourceInterface, RM\TimestampableInterface
{
    use RM\TimestampableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $theme;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var ArrayCollection|CatalogPage[]
     */
    private $pages;

    /**
     * @var array
     */
    private $options;

    /**
     * (non-mapped)
     * @var string
     */
    private $format;

    /**
     * (non-mapped)
     * @var bool
     */
    private $displayPrices;

    /**
     * (non-mapped)
     * @var ContextInterface
     */
    private $context;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->title;
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id.
     *
     * @param int $id
     *
     * @return Catalog
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns the theme.
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Sets the theme.
     *
     * @param string $theme
     *
     * @return Catalog
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return Catalog
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return Catalog
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * Sets the slug.
     *
     * @param string $slug
     *
     * @return Catalog
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Returns the pages.
     *
     * @return ArrayCollection|CatalogPage[]
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Adds the pages.
     *
     * @param CatalogPage $page
     *
     * @return Catalog
     */
    public function addPage(CatalogPage $page)
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setCatalog($this);
        }

        return $this;
    }

    /**
     * Removes the pages.
     *
     * @param CatalogPage $page
     *
     * @return Catalog
     */
    public function removePage(CatalogPage $page)
    {
        if ($this->pages->contains($page)) {
            $this->pages->removeElement($page);
            $page->setCatalog(null);
        }

        return $this;
    }

    /**
     * Returns the options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options.
     *
     * @param array $options
     *
     * @return Catalog
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Returns the format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Sets the format.
     *
     * @param string $format
     *
     * @return Catalog
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Returns whether to display prices.
     *
     * @return bool
     */
    public function isDisplayPrices()
    {
        return $this->displayPrices;
    }

    /**
     * Sets whether to display prices.
     *
     * @param bool $displayPrices
     *
     * @return Catalog
     */
    public function setDisplayPrices($displayPrices)
    {
        $this->displayPrices = (bool)$displayPrices;

        return $this;
    }

    /**
     * Returns the context.
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the context.
     *
     * @param ContextInterface $context
     *
     * @return Catalog
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }
}
