<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\TranslatableNormalizer;

/**
 * Class BrandNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandNormalizer extends TranslatableNormalizer
{
    /**
     * @inheritDoc
     *
     * @param Model\BrandInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        $data['name'] = $object->getName();
        $data['visible'] = $object->isVisible();

        if (self::contextHasGroup(['Default', 'Brand'], $context)) {
            if (null !== $seo = $object->getSeo()) {
                $data['seo'] = $seo->getId();
            }
        } elseif (self::contextHasGroup('Search', $context)) {
            if (null !== $seo = $object->getSeo()) {
                $data['seo'] = $this->normalizeObject($seo, $format, $context);
            }
        }

        return $data;
    }
}
