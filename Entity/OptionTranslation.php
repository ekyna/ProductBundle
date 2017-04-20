<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\OptionTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class OptionTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionTranslation extends AbstractTranslation implements OptionTranslationInterface
{
    protected ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): OptionTranslationInterface
    {
        $this->title = $title;

        return $this;
    }
}
