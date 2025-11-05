<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Enum\ColorInterface;
use Ekyna\Component\Resource\Enum\LabelInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class InventoryState
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
enum InventoryState: string implements LabelInterface, ColorInterface
{
    case NEW    = 'new';
    case OPENED = 'opened';
    case CLOSED = 'closed';

    public function label(): TranslatableInterface
    {
        return t('inventory.status.' . $this->value, [], 'EkynaProduct');
    }

    public function color(): string
    {
        return match ($this) {
            InventoryState::NEW    => 'brown',
            InventoryState::OPENED => 'warning',
            InventoryState::CLOSED => 'teal',
        };
    }
}
