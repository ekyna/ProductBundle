<?php

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model\TranslationInterface;
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

        $data = array_replace([
            'designation' => $product->getFullDesignation(),
            'type'        => $product->getType(),
            'reference'   => $product->getReference(),
            'visible'     => $product->isVisible(),
        ], $data);

        if (in_array('Default', $groups)) {

            // Brand
            if (null !== $brand = $product->getBrand()) {
                $data['brand'] = $brand->getId();
            }

            // Seo
            if (null !== $seo = $product->getSeo()) {
                $data['seo'] = $seo->getId();
            }

            // Categories
            $data['categories'] = array_map(function (Model\CategoryInterface $c) use ($format, $context) {
                return $c->getId();
            }, $product->getCategories()->toArray());

            // References
            $data['references'] = array_map(function (Model\ProductReferenceInterface $r) use ($format, $context) {
                return $this->normalizeObject($r, $format, $context);
            }, $product->getReferences()->toArray());

        } elseif (in_array('Search', $groups)) {

            // Brand
            if (null !== $brand = $product->getBrand()) {
                $data['brand'] = [
                    'id'   => $brand->getId(),
                    'name' => $brand->getName(),
                ];
            }

            // Seo
            if (null !== $seo = $product->getSeo()) {
                $data['seo'] = $this->normalizeObject($seo, $format, $context);
            }

            // Categories
            $data['categories'] = array_map(function (Model\CategoryInterface $c) use ($format, $context) {
                return [
                    'id'   => $c->getId(),
                    'name' => $c->getName(),
                ];
            }, $product->getCategories()->toArray());

            // References
            $data['references'] = array_map(function (Model\ProductReferenceInterface $r) use ($format, $context) {
                return $r->getNumber();
            }, $product->getReferences()->toArray());

        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function filterTranslation(TranslationInterface $translation)
    {
        /** @var Model\ProductInterface $product */
        $product = $translation->getTranslatable();

        if ($product->getType() === 'variant') {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //$resource = parent::denormalize($data, $class, $format, $context);

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
