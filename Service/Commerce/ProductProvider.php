<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ApiBundle\Action\SearchAction;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SubjectProviderInterface
{
    public const NAME = 'product';

    protected ProductRepositoryInterface $productRepository;
    private string                       $productClass;

    public function __construct(ProductRepositoryInterface $productRepository, string $productClass)
    {
        $this->productRepository = $productRepository;
        $this->productClass = $productClass;
    }

    public function assign(Reference $reference, Subject $subject): SubjectProviderInterface
    {
        return $this->transform($subject, $reference->getSubjectIdentity());
    }

    public function resolve(Reference $reference): Subject
    {
        return $this->reverseTransform($reference->getSubjectIdentity());
    }

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

    public function reverseTransform(Identity $identity): Subject
    {
        $this->assertSupportsIdentity($identity);

        $productId = $identity->getIdentifier();

        if (null !== $product = $identity->getSubject()) {
            if ((!$product instanceof $this->productClass) || ($product->getId() != $productId)) {
                throw new SubjectException('Failed to resolve item subject.');
            }

            return $product;
        }

        if (null === $product = $this->productRepository->find($productId)) {
            throw new SubjectException('Failed to resolve item subject.');
        }

        $identity->setSubject($product);

        return $product;
    }

    public function supportsSubject(Subject $subject): bool
    {
        return $subject instanceof $this->productClass;
    }

    public function supportsReference(Reference $reference): bool
    {
        return $reference->getSubjectIdentity()->getProvider() === self::NAME;
    }

    public function getRepository(): SubjectRepositoryInterface
    {
        return $this->productRepository;
    }

    public function getSubjectClass(): string
    {
        return $this->productClass;
    }

    public function getSearchActionAndParameters(string $context): array
    {
        if ($context === self::CONTEXT_ACCOUNT) {
            return [
                'route'      => 'ekyna_product_account_product_search',
                'parameters' => [],
            ];
        }

        $result = [
            'action'     => SearchAction::class,
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

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return t('product.label.singular', [], 'EkynaProduct');
    }

    /**
     * Asserts that the subject reference is supported.
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
     * @throws SubjectException
     */
    protected function assertSupportsIdentity(Identity $identity): void
    {
        if ($identity->getProvider() !== self::NAME) {
            throw new SubjectException('Unsupported subject identity.');
        }
    }
}
