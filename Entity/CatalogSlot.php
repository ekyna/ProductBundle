<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class CatalogSlot
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogSlot
{
    private ?int              $id      = null;
    private ?CatalogPage      $page    = null;
    private ?ProductInterface $product = null;
    private ?int              $number  = null;
    private array             $options;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?CatalogPage
    {
        return $this->page;
    }

    public function setPage(?CatalogPage $page): CatalogSlot
    {
        if ($page === $this->page) {
            return $this;
        }

        if ($previous = $this->page) {
            $this->page = null;
            $previous->removeSlot($this);
        }

        if ($this->page = $page) {
            $this->page->addSlot($this);
        }

        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): CatalogSlot
    {
        $this->product = $product;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): CatalogSlot
    {
        $this->number = $number;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options = []): CatalogSlot
    {
        $this->options = $options;

        return $this;
    }
}
