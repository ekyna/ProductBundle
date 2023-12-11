<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Common\Model\Units;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class InventoryProductNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryProductNormalizer implements NormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (!$object instanceof InventoryProduct) {
            throw new UnexpectedTypeException($object, InventoryProduct::class);
        }

        $product = $object->getProduct();

        $unit = $product?->getUnit() ?? Units::PIECE;

        $precision = Units::getPrecision($unit);

        $state = null;
        if (null !== $object->getRealStock()) {
            $state = 'success';
        } elseif ($object->getInitialStock()->isZero() && $product->isEndOfLife()) {
            $state = 'end-of-life';
        }

        return [
            'id'          => $object->getId(),
            'gtin'        => $product->getReferenceByType(ProductReferenceTypes::TYPE_EAN_13),
            'reference'   => $product?->getReference(),
            'designation' => $product?->getFullDesignation(),
            'geocodes'    => $object->getGeocodes(),
            'bundle'      => ProductTypes::isBundleType($product) ? 1 : 0,
            'initial'     => $object->getInitialStock()->toFixed($precision),
            'real'        => $object->getRealStock()?->toFixed($precision),
            'state'       => $state,
            'end_of_life' => $product->isEndOfLife(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof InventoryProduct && $format === 'json';
    }
}
