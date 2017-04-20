<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class AttributeChoiceTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeChoiceTranslation extends AbstractTranslation implements AttributeChoiceTranslationInterface
{
    protected ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): AttributeChoiceTranslationInterface
    {
        $this->title = $title;

        return $this;
    }
}
