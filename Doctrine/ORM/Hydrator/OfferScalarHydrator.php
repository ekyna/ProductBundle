<?php

namespace Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

/**
 * Class OfferScalarHydrator
 * @package Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferScalarHydrator extends AbstractHydrator
{
    const NAME = 'OfferScalarHydrator';


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

            switch($alias) {
                case 'id':
                case 'group_id':
                case 'country_id':
                case 'special_offer_id':
                case 'pricing_id':
                    if (!is_null($value)) {
                        $value = intval($value);
                    }
                    break;

                case 'min_qty':
                case 'percent':
                case 'net_price':
                    $value = floatval($value);
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
