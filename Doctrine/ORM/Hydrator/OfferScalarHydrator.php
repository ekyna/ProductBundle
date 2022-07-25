<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator;

use Decimal\Decimal;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

use function intval;
use function is_null;
use function json_decode;

/**
 * Class OfferScalarHydrator
 * @package Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferScalarHydrator extends AbstractHydrator
{
    public const NAME = 'OfferScalarHydrator';

    /**
     * @inheritDoc
     */
    protected function hydrateAllData(): array
    {
        $result = [];

        while ($data = $this->statement()->fetchAssociative()) {
            $this->hydrateRowData($data, $result);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function hydrateRowData(array $row, array &$result)
    {
        $tmp = [];

        foreach ($row as $key => $value) {
            $alias = $this->_rsm->getScalarAlias($key);

            $tmp[$alias] = $this->normalizeValue($alias, $value);
        }

        $result[] = $tmp;
    }

    private function normalizeValue(string $name, mixed $value): Decimal|int|bool|array|null
    {
        return match ($name) {
            'id', 'group_id', 'country_id', 'special_offer_id', 'pricing_id' => is_null($value) ? null : intval($value),
            'starting_from' => (bool)$value,
            'min_qty', 'original_price', 'sell_price', 'net_price', 'percent' => new Decimal((string)$value),
            'details' => $this->normalizeDetails($value),
            default => $value,
        };
    }

    private function normalizeDetails(string $data): array
    {
        $data = json_decode($data, true);

        foreach ($data as $key => $value) {
            $data[$key] = new Decimal((string)$value);
        }

        return $data;
    }
}
