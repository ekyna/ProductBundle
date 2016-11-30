<?php

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Serializer\AbstractTranslatableNormalizer;

/**
 * Class BrandNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandNormalizer extends AbstractTranslatableNormalizer
{
    /**
     * @inheritdoc
     */
    public function normalize($brand, $format = null, array $context = [])
    {
        $data = parent::normalize($brand, $format, $context);

        /** @var Model\BrandInterface $brand */
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        $data['name'] = $brand->getName();

        if (in_array('Default', $groups)) {
            // Seo
            if (null !== $seo = $brand->getSeo()) {
                $data['seo'] = $seo->getId();
            }
        } elseif (in_array('Search', $groups)) {
            // Seo
            if (null !== $seo = $brand->getSeo()) {
                $data['seo'] = $this->normalizeObject($seo, $format, $context);
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Model\BrandInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, Model\BrandInterface::class);
    }
}
