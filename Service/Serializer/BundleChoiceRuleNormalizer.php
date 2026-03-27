<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model\BundleChoiceRuleInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class BundleChoiceRuleNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceRuleNormalizer extends ResourceNormalizer
{
    /**
     * @param BundleChoiceRuleInterface $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'type'       => $object->getType(),
            'conditions' => $object->getConditions(),
        ];
    }
}
