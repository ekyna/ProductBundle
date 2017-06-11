<?php

namespace Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

/**
 * Class PricingGridHydrator
 * @package Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingGridHydrator extends AbstractHydrator
{
    const NAME = 'PricingGridHydrator';


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
            $tmp[$this->_rsm->getScalarAlias($key)] = $value;
        }

        $hash = implode('-', [
            $tmp['group_id'],
            $tmp['country_id'],
            $tmp['brand_id'],
        ]);

        $rule = [
            'id'       => intval($tmp['rule_id']),
            'quantity' => floatval($tmp['rule_quantity']),
            'percent'  => floatval($tmp['rule_percent']),
        ];

        if (isset($result[$hash])) {
            $result[$hash]['rules'][] = $rule;
        } else {
            $result[$hash] = [
                'name'  => $tmp['name'],
                'rules' => [$rule],
            ];
        }
    }
}
