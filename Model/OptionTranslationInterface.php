<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface OptionTranslationInterface
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OptionInterface getTranslatable()
 */
interface OptionTranslationInterface extends TranslationInterface
{
    public function getTitle(): ?string;

    public function setTitle(?string $title): OptionTranslationInterface;
}
