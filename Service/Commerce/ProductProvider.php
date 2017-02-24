<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceResolver;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Exception\SubjectException;
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
     * @var PriceResolver
     */
    private $priceResolver;

    /**
     * @var string
     */
    private $productClass;

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
     * @param PriceResolver                $priceResolver
     * @param string                       $productClass
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PriceResolver $priceResolver,
        $productClass
    ) {
        $this->productRepository = $productRepository;
        $this->priceResolver = $priceResolver;
        $this->productClass = $productClass;
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

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $subject */

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
            if ((!$product instanceof $this->productClass) || ($product->getId() != $productId)) {
                // TODO Clear identity data ?
                throw new SubjectException("Failed to resolve item subject.");
            }

            return $product;
        }

        if (null === $product = $this->productRepository->findOneById($productId)) {
            // TODO Clear identity data ?
            throw new SubjectException("Failed to resolve item subject.");
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
        return $subject instanceof $this->productClass;
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
    public function getSubjectClass()
    {
        return $this->productClass;
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
     * @throws SubjectException
     */
    protected function assertSupportsSubject($subject)
    {
        if (!$this->supportsSubject($subject)) {
            throw new SubjectException('Unsupported subject.');
        }
    }

    /**
     * Asserts that the subject relative is supported.
     *
     * @param SubjectRelativeInterface $relative
     *
     * @throws SubjectException
     */
    protected function assertSupportsRelative(SubjectRelativeInterface $relative)
    {
        if (!$this->supportsRelative($relative)) {
            throw new SubjectException('Unsupported subject relative.');
        }
    }

    /**
     * Asserts that the subject identity is supported.
     *
     * @param SubjectIdentity $identity
     *
     * @throws SubjectException
     */
    protected function assertSupportsIdentity(SubjectIdentity $identity)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        if ($identity->getProvider() != static::NAME) {
            throw new SubjectException('Unsupported subject identity.');
        }
    }
}
