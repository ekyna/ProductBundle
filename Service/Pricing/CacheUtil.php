<?php declare(strict_types=1);


namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class CacheUtil
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CacheUtil
{
    /**
     * Adds the given key to the given cache key list.
     *
     * @param array  $list
     * @param string $key
     */
    public static function addKeyToList(array &$list, string $key)
    {
        if (!in_array($key, $list, true)) {
            $list[] = $key;
        }
    }

    /**
     * Builds and returns the offer(s) cache key.
     *
     * @param ProductInterface       $product
     * @param CustomerGroupInterface $group
     * @param CountryInterface       $country
     * @param float                  $quantity
     * @param bool                   $multiple
     *
     * @return string
     *
     * @see \Ekyna\Bundle\ProductBundle\Repository\OfferRepository
     */
    public static function buildOfferKey(
        ProductInterface $product,
        CustomerGroupInterface $group,
        CountryInterface $country,
        float $quantity = null,
        bool $multiple = true
    ) {
        return self::buildOfferKeyByIds(
            $product->getId(),
            $group->getId(),
            $country->getId(),
            $quantity,
            $multiple
        );
    }

    /**
     * Builds and returns the offer(s) cache key.
     *
     * @param int   $productId
     * @param int   $groupId
     * @param int   $countryId
     * @param float $quantity
     * @param bool  $multiple
     *
     * @return string
     *
     * @see \Ekyna\Bundle\ProductBundle\EventListener\OfferEventSubscriber
     */
    public static function buildOfferKeyByIds(
        int $productId,
        int $groupId,
        int $countryId,
        float $quantity = null,
        bool $multiple = true
    ) {
        $id = sprintf(
            'product_offer%s_%d_%d_%d',
            $multiple ? 's' : '',
            $productId,
            $groupId,
            $countryId
        );

        if ($quantity) {
            $id .= '_' . intval($quantity); // TODO deal with float
        }

        return $id;
    }

    /**
     * Builds and returns the price cache key.
     *
     * @param ProductInterface       $product
     * @param CustomerGroupInterface $group
     * @param CountryInterface       $country
     *
     * @return string
     *
     * @see \Ekyna\Bundle\ProductBundle\Repository\PriceRepository
     */
    public static function buildPriceKey(
        ProductInterface $product,
        CustomerGroupInterface $group,
        CountryInterface $country
    ) {
        return self::buildPriceKeyByIds(
            $product->getId(),
            $group->getId(),
            $country->getId()
        );
    }

    /**
     * Builds and returns the price cache id.
     *
     * @param int $productId
     * @param int $groupId
     * @param int $countryId
     *
     * @return string
     *
     * @see \Ekyna\Bundle\ProductBundle\EventListener\PriceEventSubscriber
     */
    public static function buildPriceKeyByIds(
        int $productId,
        int $groupId,
        int $countryId
    ) {
        return sprintf(
            'product_price_%d_%d_%d',
            $productId,
            $groupId,
            $countryId
        );
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
