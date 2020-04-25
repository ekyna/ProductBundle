<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface as Relative;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;

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
     * @var string
     */
    private $productClass;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param string                     $productClass
     */
    public function __construct(ProductRepositoryInterface $productRepository, string $productClass)
    {
        $this->productRepository = $productRepository;
        $this->productClass = $productClass;
    }

    /**
     * @inheritDoc
     */
    public function assign(Relative $relative, Subject $subject): SubjectProviderInterface
    {
        return $this->transform($subject, $relative->getSubjectIdentity());
    }

    /**
     * @inheritDoc
     */
    public function resolve(Relative $relative): Subject
    {
        return $this->reverseTransform($relative->getSubjectIdentity());
    }

    /**
     * @inheritdoc
     */
    public function transform(Subject $subject, Identity $identity): SubjectProviderInterface
    {
        $this->assertSupportsSubject($subject);

        if ($subject === $identity->getSubject()) {
            return $this;
        }

        $identity
            ->setProvider(self::NAME)
            ->setIdentifier($subject->getId())
            ->setSubject($subject);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform(Identity $identity): Subject
    {
        $this->assertSupportsIdentity($identity);

        $productId = intval($identity->getIdentifier());

        if (null !== $product = $identity->getSubject()) {
            if ((!$product instanceof $this->productClass) || ($product->getId() != $productId)) {
                throw new SubjectException("Failed to resolve item subject.");
            }

            return $product;
        }

        if (null === $product = $this->productRepository->find($productId)) {
            throw new SubjectException("Failed to resolve item subject.");
        }

        $identity->setSubject($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function supportsSubject(Subject $subject): bool
    {
        return $subject instanceof $this->productClass;
    }

    /**
     * @inheritdoc
     */
    public function supportsRelative(Relative $relative): bool
    {
        return $relative->getSubjectIdentity()->getProvider() === self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getRepository(): SubjectRepositoryInterface
    {
        return $this->productRepository;
    }

    /**
     * @inheritdoc
     */
    public function getSubjectClass(): string
    {
        return $this->productClass;
    }

    /**
     * @inheritDoc
     */
    public function getSearchRouteAndParameters(string $context): array
    {
        if ($context === self::CONTEXT_ACCOUNT) {
            return [
                'route'      => 'ekyna_product_account_product_search',
                'parameters' => [],
            ];
        }

        $result = [
            'route'      => 'ekyna_product_product_admin_search',
            'parameters' => [],
        ];

        if ($context === self::CONTEXT_SUPPLIER) {
            $result['parameters'] = [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT,
                ],
            ];
        } elseif ($context === self::CONTEXT_ITEM) {
            $result['parameters'] = [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIABLE,
                    ProductTypes::TYPE_BUNDLE,
                    ProductTypes::TYPE_CONFIGURABLE,
                ],
            ];
        } elseif ($context === self::CONTEXT_SALE) {
            $result['parameters'] = [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT,
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
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return 'ekyna_product.product.label.singular';
    }

    /**
     * Asserts that the subject relative is supported.
     *
     * @param Subject $subject
     *
     * @throws SubjectException
     */
    protected function assertSupportsSubject(Subject $subject): void
    {
        if (!$this->supportsSubject($subject)) {
            throw new SubjectException('Unsupported subject.');
        }
    }

    /**
     * Asserts that the subject identity is supported.
     *
     * @param Identity $identity
     *
     * @throws SubjectException
     */
    protected function assertSupportsIdentity(Identity $identity): void
    {
        if ($identity->getProvider() != self::NAME) {
            throw new SubjectException('Unsupported subject identity.');
        }
    }
}
