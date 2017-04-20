<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Class Catalog
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CatalogInterface extends ResourceInterface, TimestampableInterface
{
    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): CatalogInterface;

    public function getTheme(): ?string;

    public function setTheme(?string $theme): CatalogInterface;

    public function getTitle(): ?string;

    public function setTitle(?string $title): CatalogInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): CatalogInterface;

    public function getSlug(): ?string;

    public function setSlug(?string $slug): CatalogInterface;

    /**
     * @return Collection<CatalogPage>
     */
    public function getPages(): Collection;

    public function addPage(CatalogPage $page): CatalogInterface;

    public function removePage(CatalogPage $page): CatalogInterface;

    public function getOptions(): ?array;

    public function setOptions(?array $options): CatalogInterface;

    public function getFormat(): ?string;

    public function setFormat(?string $format): CatalogInterface;

    public function isDisplayPrices(): bool;

    public function setDisplayPrices(bool $display): CatalogInterface;

    public function getContext(): ?ContextInterface;

    public function setContext(?ContextInterface $context): CatalogInterface;

    public function getTemplate(): ?string;

    public function setTemplate(string $template): CatalogInterface;

    /**
     * @return Collection<SaleItemInterface>
     */
    public function getSaleItems(): Collection;

    /**
     * @param array<SaleItemInterface> $items
     */
    public function setSaleItems(array $items): CatalogInterface;

    public function addSaleItem(SaleItemInterface $item): CatalogInterface;

    public function removeSaleItem(SaleItemInterface $item): CatalogInterface;

    /**
     * Returns whether to save the catalog render.
     */
    public function isSave(): bool;

    /**
     * Sets whether to save the catalog render.
     */
    public function setSave(bool $save): CatalogInterface;
}
