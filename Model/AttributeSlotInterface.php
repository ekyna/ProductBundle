<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface AttributeSlotInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttributeSlotInterface extends ResourceInterface, SortableInterface
{
    public function getSet(): ?AttributeSetInterface;

    public function setSet(?AttributeSetInterface $set): AttributeSlotInterface;

    public function getAttribute(): ?AttributeInterface;

    public function setAttribute(?AttributeInterface $attribute): AttributeSlotInterface;

    /**
     * Returns whether this slot's attribute is required.
     */
    public function isRequired(): bool;

    /**
     * Sets whether this slot's attribute is required.
     */
    public function setRequired(bool $required): AttributeSlotInterface;

    /**
     * Returns whether this slot's attribute is used to generate variant names and designations.
     */
    public function isNaming(): bool;

    /**
     * Sets whether this slot's attribute is used to generate variant names and designations.
     */
    public function setNaming(bool $naming): AttributeSlotInterface;
}
