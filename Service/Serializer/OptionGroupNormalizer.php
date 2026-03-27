<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\TranslatableNormalizer;

/**
 * Class OptionGroupNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupNormalizer extends TranslatableNormalizer
{
    /**
     * @param OptionGroupInterface $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'id'       => $object->getId(),
            'name'     => $object->getName(),
            'title'    => $object->getTitle(),
            'required' => $object->isRequired(),
            'options'  => $this->normalizeCollection($object->getOptions(), $format, $context),
        ];
    }

}
