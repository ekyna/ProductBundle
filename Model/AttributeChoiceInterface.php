<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface AttributeChoiceInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method AttributeChoiceTranslationInterface translate($locale = null, $create = false)
 */
interface AttributeChoiceInterface extends MediaSubjectInterface, RM\SortableInterface, RM\TranslatableInterface
{
    public function getAttribute(): ?AttributeInterface;

    public function setAttribute(?AttributeInterface $attribute): AttributeChoiceInterface;

    public function getName(): ?string;

    public function setName(?string $name): AttributeChoiceInterface;

    public function getColor(): ?string;

    public function setColor(?string $color): AttributeChoiceInterface;

    /**
     * Returns the (translated) title.
     */
    public function getTitle(): ?string;

    /**
     * Returns the (translated) title.
     */
    public function setTitle(string $title): AttributeChoiceInterface;
}
