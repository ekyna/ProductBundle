<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\TranslatableNormalizer;
use Exception;

/**
 * Class CategoryNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryNormalizer extends TranslatableNormalizer
{
    /**
     * @inheritDoc
     *
     * @param Model\CategoryInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        $data['name'] = $object->getName();
        $data['visible'] = $object->isVisible();

        if ($this->contextHasGroup(['Default', 'Category'], $context)) {
            if (null !== $seo = $object->getSeo()) {
                $data['seo'] = $seo->getId();
            }
        } elseif ($this->contextHasGroup('Search', $context)) {
            if (null !== $seo = $object->getSeo()) {
                $data['seo'] = $this->normalizeObject($seo, $format, $context);
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        //$object = parent::denormalize($data, $class, $format, $context);

        throw new Exception('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Model\CategoryInterface;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return class_exists($type) && is_subclass_of($type, Model\CategoryInterface::class);
    }
}
