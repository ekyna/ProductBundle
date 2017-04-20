<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface AttributeChoiceTranslationInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method AttributeChoiceInterface getTranslatable()
 */
interface AttributeChoiceTranslationInterface extends TranslationInterface
{
    public function getTitle(): ?string;

    public function setTitle(?string $title): AttributeChoiceTranslationInterface;
}
