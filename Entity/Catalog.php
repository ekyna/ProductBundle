<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class Catalog
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Catalog extends AbstractResource implements CatalogInterface
{
    use TimestampableTrait;

    private ?CustomerInterface $customer    = null;
    private ?string            $theme       = null;
    private ?string            $title       = null;
    private ?string            $description = null;
    private ?string            $slug        = null;
    /** @var Collection<CatalogPage> */
    private Collection $pages;
    private ?array     $options = null;

    /** Non-mapped fields */

    private ?string           $format        = null;
    private bool              $displayPrices = false;
    private ?ContextInterface $context       = null;
    private ?string           $template      = null;
    /** @var Collection<SaleItemInterface> */
    private Collection $saleItems;
    /** (non-mapped) */
    private bool $save = false;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->saleItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?: 'New catalog';
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): CatalogInterface
    {
        $this->customer = $customer;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): CatalogInterface
    {
        $this->theme = $theme;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): CatalogInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): CatalogInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): CatalogInterface
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(CatalogPage $page): CatalogInterface
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setCatalog($this);
        }

        return $this;
    }

    public function removePage(CatalogPage $page): CatalogInterface
    {
        if ($this->pages->contains($page)) {
            $this->pages->removeElement($page);
            $page->setCatalog(null);
        }

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): CatalogInterface
    {
        $this->options = $options;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): CatalogInterface
    {
        $this->format = $format;

        return $this;
    }

    public function isDisplayPrices(): bool
    {
        return $this->displayPrices;
    }

    public function setDisplayPrices(bool $display): CatalogInterface
    {
        $this->displayPrices = $display;

        return $this;
    }

    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }

    public function setContext(?ContextInterface $context): CatalogInterface
    {
        $this->context = $context;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): CatalogInterface
    {
        $this->template = $template;

        return $this;
    }

    public function getSaleItems(): Collection
    {
        return $this->saleItems;
    }

    public function setSaleItems(array $items): CatalogInterface
    {
        $this->saleItems = new ArrayCollection();

        foreach ($items as $item) {
            $this->addSaleItem($item);
        }

        return $this;
    }

    public function addSaleItem(SaleItemInterface $item): CatalogInterface
    {
        if (!$this->saleItems->contains($item)) {
            $this->saleItems->add($item);
        }

        return $this;
    }

    public function removeSaleItem(SaleItemInterface $item): CatalogInterface
    {
        if ($this->saleItems->contains($item)) {
            $this->saleItems->removeElement($item);
        }

        return $this;
    }

    public function isSave(): bool
    {
        return $this->save;
    }

    public function setSave(bool $save): CatalogInterface
    {
        $this->save = $save;

        return $this;
    }
}
