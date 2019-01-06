<?php

namespace Ekyna\Bundle\ProductBundle\Service\Serializer;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper\SubjectNormalizerHelper;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Model\TranslationInterface;
use Ekyna\Component\Resource\Serializer\AbstractTranslatableNormalizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareTrait;

/**
 * Class ProductNormalizer
 * @package Ekyna\Bundle\ProductBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductNormalizer extends AbstractTranslatableNormalizer implements CacheManagerAwareInterface
{
    use CacheManagerAwareTrait;

    /**
     * @var SubjectNormalizerHelper
     */
    protected $helper;

    /**
     * @var SupplierProductRepositoryInterface
     */
    protected $supplierProductRepository;


    /**
     * Sets the helper.
     *
     * @param SubjectNormalizerHelper $helper
     */
    public function setSubjectNormalizerHelper(SubjectNormalizerHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Sets the supplier product repository.
     *
     * @param SupplierProductRepositoryInterface $repository
     */
    public function setSupplierProductRepository(SupplierProductRepositoryInterface $repository)
    {
        $this->supplierProductRepository = $repository;
    }

    /**
     * @inheritdoc
     *
     * @param Model\ProductInterface $product
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if ($this->contextHasGroup('StockView', $context)) {
            return $this->helper->normalizeStock($product, $format, $context);
        }

        $data = parent::normalize($product, $format, $context);

        // Reference (include variant's)
        $reference = [$product->getReference()];
        if ($product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
            foreach ($product->getVariants() as $variant) {
                $reference[] = $variant->getReference();
            }
        }

        $data = array_replace([
            'designation' => $product->getFullDesignation(),
            'type'        => $product->getType(),
            'reference'   => $reference,
            'net_price'   => (float)$product->getNetPrice(),
            'min_price'   => (float)$product->getMinPrice(),
            'stock_state' => $product->getStockState(),
            'visible'     => $product->isVisible(),
            'tax_group'   => $product->getTaxGroup()->getId(),
        ], $data);

        if ($this->contextHasGroup(['Default', 'Product'], $context)) {

            // Brand
            if (null !== $brand = $product->getBrand()) {
                $data['brand'] = $brand->getId();
            }

            /** @var \Ekyna\Bundle\MediaBundle\Model\MediaInterface $image */
            if ($image = $product->getImages(true, 1)->first()) {
                $data['image'] = $this->cacheManager->getBrowserPath($image->getPath(), 'media_front');
            }

            // Seo
            if (null !== $seo = $product->getSeo()) {
                $data['seo'] = $seo->getId();
            }

            // Categories
            $data['categories'] = array_map(function (Model\CategoryInterface $c) {
                return $c->getId();
            }, $product->getCategories()->toArray());

            // References
            $data['references'] = array_map(function (Model\ProductReferenceInterface $r) use ($format, $context) {
                return $this->normalizeObject($r, $format, $context);
            }, $product->getReferences()->toArray());

        } elseif ($this->contextHasGroup('Search', $context)) {

            // Brand
            if (null !== $brand = $product->getBrand()) {
                $data['brand'] = [
                    'id'      => $brand->getId(),
                    'name'    => $brand->getName(),
                    'visible' => $brand->isVisible(),
                ];
            }

            // Seo
            if (null !== $seo = $product->getSeo()) {
                $data['seo'] = $this->normalizeObject($seo, $format, $context);
            }

            // Categories
            $data['categories'] = array_map(function (Model\CategoryInterface $c) {
                return [
                    'id'      => $c->getId(),
                    'name'    => $c->getName(),
                    'visible' => $c->isVisible(),
                ];
            }, $product->getCategories()->toArray());

            // References
            $data['references'] = array_map(function (Model\ProductReferenceInterface $r) {
                return $r->getNumber();
            }, $product->getReferences()->toArray());

        } elseif ($this->contextHasGroup('Summary', $context)) {

            // Brand
            if (null !== $brand = $product->getBrand()) {
                $data['brand'] = $brand->getName();
            }

            /** @var \Ekyna\Bundle\MediaBundle\Model\MediaInterface $image */
            if ($image = $product->getImages(true, 1)->first()) {
                $data['image'] = $this->cacheManager->getBrowserPath($image->getPath(), 'media_thumb');
            }

            $data = array_replace($data, $this->helper->normalizeStock($product, $format, $context));

            $data['suppliers'] = array_map(function(SupplierProductInterface $reference) {
                return [
                    'name' => $reference->getSupplier()->getName(),
                    'price' => $reference->getNetPrice(),
                    'currency' => $reference->getSupplier()->getCurrency()->getCode(),
                ];
            }, $this->supplierProductRepository->findBySubject($product));
        }

        if ($this->contextHasGroup('Stock', $context)) {
            $data = array_replace($this->helper->normalizeStock($product, $format, $context), $data);
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
