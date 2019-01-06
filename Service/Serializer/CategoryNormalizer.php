<?php

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Serializer\AbstractTranslatableNormalizer;

/**
 * Class CategoryNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryNormalizer extends AbstractTranslatableNormalizer
{
    /**
     * @inheritdoc
     *
     * @param Model\CategoryInterface $category
     */
    public function normalize($category, $format = null, array $context = [])
    {
        $data = parent::normalize($category, $format, $context);

        $data['name'] = $category->getName();
        $data['visible'] = $category->isVisible();

        if ($this->contextHasGroup(['Default', 'Category'], $context)) {
            if (null !== $seo = $category->getSeo()) {
                $data['seo'] = $seo->getId();
            }
        } elseif ($this->contextHasGroup('Search', $context)) {
            if (null !== $seo = $category->getSeo()) {
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
        //$object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Model\CategoryInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, Model\CategoryInterface::class);
    }
}
