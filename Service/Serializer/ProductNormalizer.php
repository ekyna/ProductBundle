<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper\SubjectNormalizerHelper;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\TranslatableNormalizer;
use Ekyna\Component\Resource\Model\TranslationInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareTrait;

/**
 * Class ProductNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductNormalizer extends TranslatableNormalizer implements CacheManagerAwareInterface
{
    use CacheManagerAwareTrait;

    protected SubjectNormalizerHelper            $helper;
    protected SupplierProductRepositoryInterface $supplierProductRepository;

    public function setSubjectNormalizerHelper(SubjectNormalizerHelper $helper): void
    {
        $this->helper = $helper;
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
    public function normalize($object, $format = null, array $context = [])
    {
        if ($this->contextHasGroup('StockView', $context)) {
            return $this->helper->normalizeStock($object, $format, $context);
        }

        $data = parent::normalize($object, $format, $context);

        // Reference (include variant's)
        $reference = [$object->getReference()];
        if ($object->getType() === Model\ProductTypes::TYPE_VARIABLE) {
            foreach ($object->getVariants() as $variant) {
                $reference[] = $variant->getReference();
            }
        }

        $data = array_replace([
            'designation' => $object->getFullDesignation(),
            'type'        => $object->getType(),
            'reference'   => $reference,
            'net_price'   => $object->getNetPrice()->toFixed(5),
            'min_price'   => $object->getMinPrice()->toFixed(5),
            'stock_state' => $object->getStockState(),
            'visible'     => $object->isVisible(),
            'tax_group'   => $object->getTaxGroup()->getId(),
        ], $data);

        if ($this->contextHasGroup(['Default', 'Product'], $context)) {
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
        } elseif ($this->contextHasGroup('Search', $context)) {
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

            // Option groups
            $data['option_groups'] = $this->normalizeOptionGroups($object);
            $data['quote_only'] = $object->isQuoteOnly();
            $data['end_of_life'] = $object->isEndOfLife();
        } elseif ($this->contextHasGroup('Summary', $context)) {
            $data['visibility'] = $object->getVisibility();

            // Brand
            if (null !== $brand = $object->getBrand()) {
                $data['brand'] = $brand->getName();
            }

            // Image
            if ($image = $object->getImage()) {
                $data['image'] = $this->cacheManager->getBrowserPath($image->getPath(), 'media_thumb');
            }

            $data = array_replace($data, $this->helper->normalizeStock($object, $format, $context));

            $data['suppliers'] = array_map(function (SupplierProductInterface $reference) {
                return [
                    'name'      => $reference->getSupplier()->getName(),
                    'net_price' => $reference->getNetPrice()->toFixed(5),
                    'currency'  => $reference->getSupplier()->getCurrency()->getCode(),
                ];
            }, $this->supplierProductRepository->findBySubject($object));
        }

        if ($this->contextHasGroup('Stock', $context)) {
            $data = array_replace($this->helper->normalizeStock($object, $format, $context), $data);
        }

        return $data;
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

    /**
     * @inheritDoc
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        //$resource = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Model\ProductInterface;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return class_exists($type) && is_subclass_of($type, Model\ProductInterface::class);
    }
}
