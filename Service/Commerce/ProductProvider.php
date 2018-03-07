<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceResolver;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Exception\SubjectException;
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
    protected $productRepository;

    /**
     * @var PriceResolver
     */
    protected $priceResolver;

    /**
     * @var string
     */
    private $productClass;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param PriceResolver              $priceResolver
     * @param string                     $productClass
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
        return $this->transform($subject, $relative->getSubjectIdentity());
    }

    /**
     * @inheritDoc
     */
    public function resolve(SubjectRelativeInterface $relative)
    {
        return $this->reverseTransform($relative->getSubjectIdentity());
    }

    /**
     * @inheritdoc
     */
    public function transform($subject, SubjectIdentity $identity)
    {
        $this->assertSupportsSubject($subject);

        if ($subject === $identity->getSubject()) {
            return $this;
        }

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $subject */
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

        $productId = intval($identity->getIdentifier());

        if (null !== $product = $identity->getSubject()) {
            if ((!$product instanceof $this->productClass) || ($product->getId() != $productId)) {
                throw new SubjectException("Failed to resolve item subject.");
            }

            return $product;
        }

        if (null === $product = $this->productRepository->findOneById($productId)) {
            throw new SubjectException("Failed to resolve item subject.");
        }

        $identity->setSubject($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function supportsSubject($subject)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $subject */
        return $subject instanceof $this->productClass;
    }

    /**
     * @inheritdoc
     */
    public function supportsRelative(SubjectRelativeInterface $relative)
    {
        return $relative->getSubjectIdentity()->getProvider() === self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getRepository()
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
     * @inheritDoc
     */
    public function getSearchRouteAndParameters($context)
    {
        $result = [
            'route'      => 'ekyna_product_product_admin_search',
            'parameters' => [],
        ];

        if ($context === static::CONTEXT_SUPPLIER) {
            $result['parameters'] = [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT
                ],
            ];
        } else if ($context === static::CONTEXT_SALE) {
            $result['parameters'] = [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIABLE,
                    ProductTypes::TYPE_BUNDLE,
                    ProductTypes::TYPE_CONFIGURABLE,
                ],
            ];
        }

        return $result;
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
        if ($identity->getProvider() != static::NAME) {
            throw new SubjectException('Unsupported subject identity.');
        }
    }
}
