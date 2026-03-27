<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\TranslatableNormalizer;

/**
 * Class BundleSlotNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotNormalizer extends TranslatableNormalizer
{
    /**
     * @param BundleSlotInterface $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'id'          => $object->getId(),
            'title'       => $object->getTitle(),
            'description' => $object->getDescription(),
            'required'    => $object->isRequired(),
            'choices'     => $this->normalizeCollection($object->getChoices(), $format, $context),
            'rules'       => $this->normalizeCollection($object->getRules(), $format, $context),
        ];
    }
}
