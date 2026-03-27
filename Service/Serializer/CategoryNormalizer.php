<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\TranslatableNormalizer;

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
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        $data['name'] = $object->getName();
        $data['visible'] = $object->isVisible();

        if (self::contextHasGroup(['Default', 'Category'], $context)) {
            if (null !== $seo = $object->getSeo()) {
                $data['seo'] = $seo->getId();
            }
            return $data;
        }

        if (self::contextHasGroup('Api', $context)) {
            $data['title'] = $object->getTitle();

            return $data;
        }

        if (self::contextHasGroup('Sale', $context)) {
            $data['title'] = $object->getTitle();

            return $data;
        }

        if (self::contextHasGroup('Search', $context)) {
            if (null !== $seo = $object->getSeo()) {
                $data['seo'] = $this->normalizeObject($seo, $format, $context);
            }

            return $data;
        }

        return $data;
    }
}
