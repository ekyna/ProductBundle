<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model\BundleSlotRuleInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class BundleSlotRuleNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotRuleNormalizer extends ResourceNormalizer
{
    /**
     * @param BundleSlotRuleInterface $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'type'       => $object->getType(),
            'conditions' => $object->getConditions(),
        ];
    }
}
