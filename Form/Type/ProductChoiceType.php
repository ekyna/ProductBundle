<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @deprecated Use ProductSearchType
 */
class ProductChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'resource'      => 'ekyna_product.product',
                'types'         => [],
                'query_builder' => function (Options $options) {
                    return function (EntityRepository $er) use ($options) {
                        $qb = $er->createQueryBuilder('p');

                        if (!empty($types = $options['types'])) {
                            $qb->andWhere($qb->expr()->in('p.type', $types));
                        }

                        return $qb
                            ->addOrderBy('p.designation', 'DESC')
                            ->addOrderBy('p.attributesDesignation', 'DESC');
                    };
                },
                'choice_value'  => function (?ProductInterface $product) {
                    return $product ? $product->getId() : null;
                },
                'choice_label'  => function (?ProductInterface $product) {
                    if (!$product) {
                        return null;
                    }

                    return sprintf(
                        '[%s] %s',
                        $product->getReference(),
                        $product->getFullDesignation(true)
                    );
                },
            ])
            ->setAllowedTypes('types', 'array')
            ->setAllowedValues('types', function ($value) {
                foreach ($value as $type) {
                    if (!ProductTypes::isValid($type)) {
                        return false;
                    }
                }

                return true;
            });
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
