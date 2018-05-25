<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class CatalogSlot
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogSlot
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var CatalogPage
     */
    private $page;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var int
     */
    private $number;

    /**
     * @var array
     */
    private $options;


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
     * Returns the page.
     *
     * @return CatalogPage
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the page.
     *
     * @param CatalogPage $page
     *
     * @return CatalogSlot
     */
    public function setPage(CatalogPage $page = null)
    {
        if ($page !== $this->page) {
            if ($previous = $this->page) {
                $this->page = null;
                $previous->removeSlot($this);
            }

            if ($this->page = $page) {
                $this->page->addSlot($this);
            }
        }

        return $this;
    }

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return CatalogSlot
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Returns the number.
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Sets the number.
     *
     * @param int $number
     *
     * @return CatalogSlot
     */
    public function setNumber($number)
    {
        $this->number = $number;

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
     * @return CatalogSlot
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }
}
