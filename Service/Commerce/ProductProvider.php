<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceResolver;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Builder\FormBuilderInterface;
use Ekyna\Component\Commerce\Subject\Builder\ItemBuilderInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SubjectProviderInterface
{
    const NAME = 'product';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockUnitRepositoryInterface
     */
    private $stockUnitRepository;

    /**
     * @var PriceResolver
     */
    private $priceResolver;

    /**
     * @var ItemBuilder
     */
    private $itemBuilder;

    /**
     * @var FormBuilder
     */
    private $formBuilder;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface   $productRepository
     * @param StockUnitRepositoryInterface $stockUnitRepository
     * @param PriceResolver                $priceResolver
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockUnitRepositoryInterface $stockUnitRepository,
        PriceResolver $priceResolver
    ) {
        $this->productRepository = $productRepository;
        $this->stockUnitRepository = $stockUnitRepository;
        $this->priceResolver = $priceResolver;
    }

    /**
     * @inheritDoc
     */
    public function assign(SubjectRelativeInterface $relative, $subject)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->transform($subject, $relative->getSubjectIdentity());
    }

    /**
     * @inheritDoc
     */
    public function resolve(SubjectRelativeInterface $relative)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->reverseTransform($relative->getSubjectIdentity());
    }

    /**
     * @inheritdoc
     */
    public function transform($subject, SubjectIdentity $identity)
    {
        $this->assertSupportsSubject($subject);

        /** @noinspection PhpInternalEntityUsedInspection */
        if ($subject === $identity->getSubject()) {
            return $this;
        }

        /** @var ProductInterface $subject */

        /** @noinspection PhpInternalEntityUsedInspection */
        $identity
            ->setProvider(static::NAME)
            ->setIdentifier($subject->getId())
            ->setSubject($subject);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform(SubjectIdentity $identity)
    {
        $this->assertSupportsIdentity($identity);

        /** @noinspection PhpInternalEntityUsedInspection */
        $productId = intval($identity->getIdentifier());

        /** @noinspection PhpInternalEntityUsedInspection */
        if (null !== $product = $identity->getSubject()) {
            if ((!$product instanceof ProductInterface) || ($product->getId() != $productId)) {
                // TODO Clear identity data ?
                throw new InvalidArgumentException("Failed to resolve item subject.");
            }

            return $product;
        }

        if (null === $product = $this->productRepository->findOneById($productId)) {
            // TODO Clear identity data ?
            throw new InvalidArgumentException("Failed to resolve item subject.");
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        $identity->setSubject($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function supportsSubject($subject)
    {
        return $subject instanceof ProductInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsRelative(SubjectRelativeInterface $relative)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $relative->getSubjectIdentity()->getProvider() === self::NAME;
    }

    /**
     * Returns the item builder.
     *
     * @return ItemBuilderInterface
     */
    public function getItemBuilder()
    {
        if (null !== $this->itemBuilder) {
            return $this->itemBuilder;
        }

        return $this->itemBuilder = new ItemBuilder($this, $this->priceResolver);
    }

    /**
     * Returns the form builder.
     *
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        if (null !== $this->formBuilder) {
            return $this->formBuilder;
        }

        return $this->formBuilder = new FormBuilder($this);
    }

    /**
     * @inheritdoc
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * @inheritdoc
     */
    public function getStockUnitRepository()
    {
        return $this->stockUnitRepository;
    }

    /**
     * @inheritdoc
     */
    public function getStockUnitChangeEventName()
    {
        return ProductEvents::STOCK_UNIT_CHANGE;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return 'ekyna_product.product.label.singular';
    }

    /**
     * Asserts that the subject relative is supported.
     *
     * @param mixed $subject
     *
     * @throws InvalidArgumentException
     */
    protected function assertSupportsSubject($subject)
    {
        if (!$this->supportsSubject($subject)) {
            throw new InvalidArgumentException('Unsupported subject.');
        }
    }

    /**
     * Asserts that the subject relative is supported.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @throws InvalidArgumentException
     */
    protected function assertSupportsRelative(SubjectRelativeInterface $relative)
    {
        if (!$this->supportsRelative($relative)) {
            throw new InvalidArgumentException('Unsupported subject relative.');
        }
    }

    /**
     * Asserts that the subject identity is supported.
     *
     * @param SubjectIdentity $identity
     *
     * @throws InvalidArgumentException
     */
    protected function assertSupportsIdentity(SubjectIdentity $identity)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        if ($identity->getProvider() != static::NAME) {
            throw new InvalidArgumentException('Unsupported subject identity.');
        }
    }
}
