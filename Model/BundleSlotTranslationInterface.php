<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface BundleSlotTranslationInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method BundleSlotInterface getTranslatable()
 */
interface BundleSlotTranslationInterface extends TranslationInterface
{
    public function getTitle(): ?string;

    public function setTitle(?string $title): BundleSlotTranslationInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): BundleSlotTranslationInterface;
}
