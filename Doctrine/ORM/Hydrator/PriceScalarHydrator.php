<?php

namespace Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

/**
 * Class PriceScalarHydrator
 * @package Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceScalarHydrator extends AbstractHydrator
{
    const NAME = 'PriceScalarHydrator';


    /**
     * @inheritdoc
     */
    protected function hydrateAllData()
    {
        $result = [];

        foreach ($this->_stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $this->hydrateRowData($row, $result);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function hydrateRowData(array $data, array &$result)
    {
        $tmp = [];

        foreach ($data as $key => $value) {
            $alias = $this->_rsm->getScalarAlias($key);

            switch ($alias) {
                case 'id':
                case 'group_id':
                case 'country_id':
                    if (!is_null($value)) {
                        $value = intval($value);
                    }
                    break;

                case 'starting_from':
                    $value = (bool)$value;
                    break;

                case 'original_price':
                case 'sell_price':
                case 'percent':
                    $value = floatval($value);
                    break;

                case 'ends_at':
                    $value = $value ? new \DateTime($value) : null;
                    break;

                case 'details':
                    $value = json_decode($value, true);
                    break;
            }

            $tmp[$alias] = $value;
        }

        $result[] = $tmp;
    }
}
