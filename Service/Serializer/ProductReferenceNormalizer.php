<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Exception;

/**
 * Class ProductReferenceNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReferenceNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param Model\ProductReferenceInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id'   => $object->getId(),
            'type' => $object->getType(),
            'code' => $object->getCode(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        throw new Exception('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Model\ProductReferenceInterface;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return class_exists($type) && is_subclass_of($type, Model\ProductReferenceInterface::class);
    }
}
