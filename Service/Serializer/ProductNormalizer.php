<?php

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Serializer\AbstractTranslatableNormalizer;

/**
 * Class ProductNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductNormalizer extends AbstractTranslatableNormalizer
{
    /**
     * @inheritdoc
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = parent::normalize($product, $format, $context);

        /** @var Model\ProductInterface $product */

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        $designation = $product->getDesignation();
        if (0 == strlen($designation) && $product->getType() === Model\ProductTypes::TYPE_VARIANT) {
            $designation = $product->getAttributesDesignation();
        }

        $data = array_replace([
            'designation' => $designation,
            'type'        => $product->getType(),
            'reference'   => $product->getReference(),
        ], $data);

        if (in_array('Default', $groups)) {

            $data['brand'] = $product->getBrand()->getId();

            $data['category'] = $product->getCategory()->getId();

            $data['references'] = array_map(function (Model\ProductReferenceInterface $r) use ($format, $context) {
                return $this->normalizeObject($r, $format, $context);
            }, $product->getReferences()->toArray());

        } elseif (in_array('Search', $groups)) {

            $brand = $product->getBrand();
            $data['brand'] = [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
            ];

            $category = $product->getCategory();
            $data['category'] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ];

            $data['references'] = array_map(function (Model\ProductReferenceInterface $r) use ($format, $context) {
                return $r->getNumber();
            }, $product->getReferences()->toArray());

        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $resource = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Model\ProductInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, Model\ProductInterface::class);
    }
}
