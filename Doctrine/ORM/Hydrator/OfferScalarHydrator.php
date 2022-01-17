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

    /**
     * @param string $name
     * @param mixed value
     *
     * @return array|bool|Decimal|int|null
     */
    private function normalizeValue(string $name, $value)
    {
        switch ($name) {
            case 'id':
            case 'group_id':
            case 'country_id':
            case 'special_offer_id':
            case 'pricing_id':
                if (is_null($value)) {
                    return null;
                }

                return intval($value);

            case 'starting_from':
                return (bool)$value;

            case 'min_qty':
            case 'original_price':
            case 'sell_price':
            case 'net_price':
            case 'percent':
                return new Decimal((string)$value);

            case 'details':
                return $this->normalizeDetails($value);
        }

        return $value;
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
