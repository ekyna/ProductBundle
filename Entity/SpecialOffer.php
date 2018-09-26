<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\TaggedEntityTrait;
use Ekyna\Component\Resource\Model\TrackAssociationTrait;

/**
 * Class SpecialOffer
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOffer implements SpecialOfferInterface
{
    const REL_PRODUCTS  = 'products';
    const REL_BRANDS    = 'brands';
    const REL_GROUPS    = 'groups';
    const REL_COUNTRIES = 'countries';

    use TaggedEntityTrait,
        TrackAssociationTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $percent;

    /**
     * @var int
     */
    protected $minQuantity;

    /**
     * @var \DateTime
     */
    protected $startsAt;

    /**
     * @var \DateTime
     */
    protected $endsAt;

    /**
     * @var bool
     */
    protected $stack;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var ArrayCollection|ProductInterface[]
     */
    protected $products;

    /**
     * @var ArrayCollection|BrandInterface[]
     */
    protected $brands;

    /**
     * @var ArrayCollection|CustomerGroupInterface[]
     */
    protected $groups;

    /**
     * @var ArrayCollection|CountryInterface[]
     */
    protected $countries;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->percent = 0;
        $this->minQuantity = 1;
        $this->stack = true;
        $this->enabled = false;

        $this->products = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->countries = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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
    }

    /**
     * @inheritdoc
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @inheritdoc
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setMinQuantity($quantity)
    {
        $this->minQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStartsAt()
    {
        return $this->startsAt;
    }

    /**
     * @inheritdoc
     */
    public function setStartsAt(\DateTime $date = null)
    {
        $this->startsAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEndsAt()
    {
        return $this->endsAt;
    }

    /**
     * @inheritdoc
     */
    public function setEndsAt(\DateTime $date = null)
    {
        $this->endsAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isStack()
    {
        return $this->stack;
    }

    /**
     * @inheritdoc
     */
    public function setStack(bool $stack)
    {
        $this->stack = $stack;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->product = $product;
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @inheritdoc
     */
    public function addProduct(ProductInterface $product)
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeProduct(ProductInterface $product)
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBrands()
    {
        return $this->brands;
    }

    /**
     * @inheritdoc
     */
    public function addBrand(BrandInterface $brand)
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeBrand(BrandInterface $brand)
    {
        if ($this->brands->contains($brand)) {
            $this->brands->removeElement($brand);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @inheritdoc
     */
    public function addGroup(CustomerGroupInterface $group)
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeGroup(CustomerGroupInterface $group)
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @inheritdoc
     */
    public function addCountry(CountryInterface $country)
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCountry(CountryInterface $country)
    {
        if ($this->countries->contains($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    /**
     * Post load lifecycle event handler.
     */
    public function onPostLoad()
    {
        $this->takeSnapshot();
    }

    /**
     * @inheritDoc
     */
    public static function getAssociationsProperties()
    {
        return [
            static::REL_PRODUCTS,
            static::REL_BRANDS,
            static::REL_GROUPS,
            static::REL_COUNTRIES,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getEntityTagPrefix()
    {
        return 'ekyna_product.special_offer';
    }
}
