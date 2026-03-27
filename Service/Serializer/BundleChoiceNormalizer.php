<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Ekyna\Component\Resource\Helper\ResourceHelperAwareInterface;
use Ekyna\Component\Resource\Helper\ResourceHelperAwareTrait;

/**
 * Class BundleChoiceNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceNormalizer extends ResourceNormalizer implements ResourceHelperAwareInterface
{
    use ResourceHelperAwareTrait;

    /**
     * @param BundleChoiceInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $product = $object->getProduct();

        return [
            'product'                => [
                'id'        => $product->getId(),
                'reference' => $product->getReference(),
                '_links'    => [
                    'self' => [
                        'href' => $this->getResourceHelper()->generateResourcePath($product, 'api_read', [], true),
                    ],
                ]
            ],
            'min_quantity'           => $object->getMinQuantity()->toFixed(),
            'max_quantity'           => $object->getMaxQuantity()?->toFixed(),
            'excluded_option_groups' => $object->getExcludedOptionGroups(),
            'net_price'              => $object->getNetPrice()?->toFixed(),
            'hidden'                 => $object->isHidden(),
            'rules'                  => $this->normalizeCollection($object->getRules(), $format, $context),
        ];
    }
}
