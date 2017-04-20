<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class OptionGroupTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OptionGroupInterface getTranslatable()
 */
class OptionGroupTranslation extends AbstractTranslation implements OptionGroupTranslationInterface
{
    protected ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): OptionGroupTranslationInterface
    {
        $this->title = $title;

        return $this;
    }
}
