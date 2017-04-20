<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\BundleSlotTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class BundleSlotTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotTranslation extends AbstractTranslation implements BundleSlotTranslationInterface
{
    protected ?string $title = null;
    protected ?string $description = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): BundleSlotTranslationInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): BundleSlotTranslationInterface
    {
        $this->description = $description;

        return $this;
    }
}
