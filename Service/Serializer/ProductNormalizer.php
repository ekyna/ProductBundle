<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceGridHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper\SubjectNormalizerHelper;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\TranslatableNormalizer;
use Ekyna\Component\Resource\Helper\ResourceHelperAwareInterface;
use Ekyna\Component\Resource\Helper\ResourceHelperAwareTrait;
use Ekyna\Component\Resource\Model\TranslationInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareTrait;

use function array_map;
use function array_merge_recursive;
use function array_replace;

/**
 * Class ProductNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductNormalizer
    extends TranslatableNormalizer
    implements ResourceHelperAwareInterface,
               CacheManagerAwareInterface
{
    use ResourceHelperAwareTrait;
    use CacheManagerAwareTrait;

    protected SubjectNormalizerHelper            $subjectHelper;
    protected SupplierProductRepositoryInterface $supplierProductRepository;

    public function setSubjectNormalizerHelper(SubjectNormalizerHelper $helper): void
    {
        $this->subjectHelper = $helper;
    }

    public function setSupplierProductRepository(SupplierProductRepositoryInterface $repository): void
    {
        $this->supplierProductRepository = $repository;
    }

    /**
     * @inheritDoc
     *
     * @param Model\ProductInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (self::contextHasGroup([Group::STOCK_VIEW, Group::STOCK_UNIT], $context)) {
            return $this->subjectHelper->normalizeStock($object, $format, $context);
        }

        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup('Search', $context)) {
            return $this->normalizeForSearch($object, $data);
        }

        if (self::contextHasGroup(['Summary'], $context)) {
            return $this->normalizeForSummary($object, $data, $format, $context);
        }

        if (self::contextHasGroup(['Api'], $context)) {
            return $this->normalizeForApi($object, $data, $format, $context);
        }

        $data = array_replace([
            'designation' => $object->getFullDesignation(),
            'type'        => $object->getType(),
            'reference'   => $this->getReferences($object),
            'net_price'   => $object->getNetPrice()->toFixed(5),
            'min_price'   => $object->getMinPrice()->toFixed(5),
            'stock_state' => $object->getStockState(),
            'visible'     => $object->isVisible(),
            'tax_group'   => $object->getTaxGroup()->getId(),
        ], $data);

        if (self::contextHasGroup(['Default', 'Product'], $context)) {
            // Brand
            if (null !== $brand = $object->getBrand()) {
                $data['brand'] = $brand->getId();
            }

            // Image
            if ($image = $object->getImage()) {
                $data['image'] = $this->cacheManager->getBrowserPath($image->getPath(), 'media_front');
            }

            // Seo
            if (null !== $seo = $object->getSeo()) {
                $data['seo'] = $seo->getId();
            }

            // Categories
            $data['categories'] = array_map(function (Model\CategoryInterface $c) {
                return $c->getId();
            }, $object->getCategories()->toArray());

            // References
            $data['references'] = array_map(function (Model\ProductReferenceInterface $r) use ($format, $context) {
                return $this->normalizeObject($r, $format, $context);
            }, $object->getReferences()->toArray());

            // Option groups
            $data['option_groups'] = $this->normalizeOptionGroups($object);

            return $data;
        }

        if (self::contextHasGroup('Sale', $context)) {
            $data['reference'] = $object->getReference();
            $data['brand_naming'] = $object->isBrandNaming();

            // Brand
            $data['brand'] = $object->getBrand()?->getTitle() ?? '';

            // Image
            if ($image = $object->getImage()) {
                $data['image'] = $this->cacheManager->getBrowserPath($image->getPath(), 'sale_add_thumb');
            }

            return $data;
        }

        return $data;
    }

    protected function getReferences(Model\ProductInterface $object): array
    {
        // Reference (include variant's)
        $references = [$object->getReference()];
        if ($object->getType() === Model\ProductTypes::TYPE_VARIABLE) {
            foreach ($object->getVariants() as $variant) {
                $references[] = $variant->getReference();
            }
        }

        return $references;
    }

    protected function normalizeForSearch(Model\ProductInterface $object, array $data): array
    {
        $data = array_replace($data, [
            'designation' => $object->getFullDesignation(),
            'type'        => $object->getType(),
            'reference'   => $this->getReferences($object),
            'netPrice'    => $object->getNetPrice()->toFixed(5),
            'minPrice'    => $object->getMinPrice()->toFixed(5),
            'stockState'  => $object->getStockState(),
            'visible'     => $object->isVisible(),
            'taxGroup'    => $object->getTaxGroup()->getId(),
        ]);

        // Brand
        if (null !== $brand = $object->getBrand()) {
            $data['brand'] = [
                'id'      => $brand->getId(),
                'name'    => $brand->getName(),
                'visible' => $brand->isVisible(),
            ];
        }

        // Seo
        /*if (null !== $seo = $product->getSeo()) {
            $data['seo'] = $this->normalizeObject($seo, $format, $context);
        }*/

        // Categories
        $data['categories'] = array_map(function (Model\CategoryInterface $c) {
            return [
                'id'      => $c->getId(),
                'name'    => $c->getName(),
                'visible' => $c->isVisible(),
            ];
        }, $object->getCategories()->toArray());

        // References
        $data['references'] = array_map(function (Model\ProductReferenceInterface $r) {
            return $r->getCode();
        }, $object->getReferences()->toArray());

        // References
        $data['referencesAliases'] = $object->getReferenceAliases();

        // Option groups
        $data['optionGroups'] = $this->normalizeOptionGroups($object);
        $data['quoteOnly'] = $object->isQuoteOnly();
        $data['endOfLife'] = $object->isEndOfLife();

        return $data;
    }

    protected function normalizeForSummary(
        Model\ProductInterface $object,
        array                  $data,
        string                 $format,
        array                  $context
    ): array {
        $data = array_replace($data, [
            'designation' => $object->getFullDesignation(),
            'type'        => $object->getType(),
            'reference'  => $this->getReferences($object),
            'net_price'   => $object->getNetPrice()->toFixed(5),
            'min_price'   => $object->getMinPrice()->toFixed(5),
            'stock_state' => $object->getStockState(),
            'visible'     => $object->isVisible(),
            'visibility' => $object->getVisibility(),
            'tax_group'   => $object->getTaxGroup()->getId(),
            'brand'      => $object->getBrand()?->getName(),
            'image'      => null,
        ]);

        // Image
        if ($image = $object->getImage()) {
            $data['image'] = $this->cacheManager->getBrowserPath($image->getPath(), 'media_thumb');
        }

        $stockContext = array_merge_recursive($context, ['groups' => [Group::STOCK_VIEW]]);
        $data = array_replace($data, $this->subjectHelper->normalizeStock($object, $format, $stockContext));

        $data['suppliers'] = array_map(function (SupplierProductInterface $reference) {
            return [
                'name'      => $reference->getSupplier()->getName(),
                'net_price' => $reference->getNetPrice()->toFixed(5),
                'currency'  => $reference->getSupplier()->getCurrency()->getCode(),
            ];
        }, $this->supplierProductRepository->findBySubject($object));

        $data['price_grids'] = PriceGridHelper::getPriceGrids($object);

        return $data;
    }

    protected function normalizeForApi(
        Model\ProductInterface $object,
        array                  $data,
        string                 $format,
        array                  $context
    ): array {
        $data = array_replace($data, [
            'designation'   => $object->getFullDesignation(),
            'type'          => $object->getType(),
            'reference'     => $object->getReference(),
            'net_price'     => $object->getNetPrice()->toFixed(5),
            'min_price'     => $object->getMinPrice()->toFixed(5),
            'stock_state'   => $object->getStockState(),
            'visible'       => $object->isVisible(),
            'visibility'    => $object->getVisibility(),
            'tax_group'     => $object->getTaxGroup()->getId(),
            'brand'         => $this->normalizeObject($object->getBrand(), $format, $context),
            'categories'    => $this->normalizeCollection($object->getCategories(), $format, $context),
            'references'    => $this->normalizeCollection($object->getReferences(), $format, $context),
            'option_groups' => $this->normalizeCollection($object->getOptionGroups(), $format, $context),
            'bundle_slots'  => $this->normalizeCollection($object->getBundleSlots(), $format, $context),
            'images'        => [],
        ]);

        // Image
        foreach ($object->getImages() as $image) {
            $data['images'][] = $this->cacheManager->getBrowserPath($image->getPath(), 'media_front');
        }

        $data['_links'] = [
            'self' => [
                'href' => $this->getResourceHelper()->generateResourcePath($object, 'api_read', [], true),
            ],
        ];

        // Stock data
        $stockContext = array_merge_recursive($context, ['groups' => [Group::STOCK_DATA]]);

        return array_replace($data, $this->subjectHelper->normalizeStock($object, $format, $stockContext));
    }

    /**
     * Normalizes the product option groups.
     */
    protected function normalizeOptionGroups(Model\ProductInterface $product): array
    {
        return array_map(function (Model\OptionGroupInterface $g) {
            return [
                'id'       => $g->getId(),
                'name'     => $g->getName(),
                'required' => $g->isRequired(),
            ];
        }, $product->resolveOptionGroups([], true));
    }

    /**
     * @inheritDoc
     */
    protected function filterTranslation(TranslationInterface $translation): bool
    {
        /** @var Model\ProductInterface $product */
        $product = $translation->getTranslatable();

        if ($product->getType() === 'variant') {
            return false;
        }

        return true;
    }
}
