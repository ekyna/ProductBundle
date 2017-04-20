<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\AttributeTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class AttributeTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeTranslation extends AbstractTranslation implements AttributeTranslationInterface
{
    protected ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): AttributeTranslationInterface
    {
        $this->title = $title;

        return $this;
    }
}
