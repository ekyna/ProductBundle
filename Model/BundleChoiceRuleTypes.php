<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class BundleChoiceRuleTypes
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceRuleTypes extends AbstractConstants
{
    const TYPE_DISABLED = 'disabled';
    const TYPE_OPTIONAL = 'optional';


    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_product.bundle_choice_rule.type.';

        return [
            static::TYPE_DISABLED => [$prefix . static::TYPE_DISABLED],
            static::TYPE_OPTIONAL => [$prefix . static::TYPE_OPTIONAL],
        ];
    }
}
