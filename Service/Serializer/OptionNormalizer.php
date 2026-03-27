<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\TranslatableNormalizer;
use Ekyna\Component\Resource\Helper\ResourceHelperAwareInterface;
use Ekyna\Component\Resource\Helper\ResourceHelperAwareTrait;

/**
 * Class OptionNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionNormalizer extends TranslatableNormalizer implements ResourceHelperAwareInterface
{
    use ResourceHelperAwareTrait;

    /**
     * @param OptionInterface $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $product = $object->getProduct();

        return [
            'id'          => $object->getId(),
            'product'     => $product ? [
                'id'        => $product->getId(),
                'reference' => $product->getReference(),
                '_links'    => [
                    'self' => [
                        'href' => $this->getResourceHelper()->generateResourcePath($product, 'api_read', [], true),
                    ],
                ]
            ] : null,
            'reference'   => $object->getReference(),
            'designation' => $object->getDesignation(),
            'title'       => $object->getTitle(),
            'weight'      => $object->getWeight()?->toFixed(3),
            'net_price'   => $object->getNetPrice()?->toFixed(2),
            'cascade'     => $object->isCascade(),
            'position'    => $object->getPosition(),
            // TODO tax_group
        ];
    }
}
