<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $productClass;


    /**
     * Constructor.
     *
     * @param string $brandClass
     */
    public function __construct($brandClass)
    {
        $this->productClass = $brandClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'         => function (Options $options) {
                    return 'ekyna_product.product.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'class'         => $this->productClass,
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
                'choice_value' => function(ProductInterface $product) {
                    return $product->getId();
                },
                'choice_label' => function(ProductInterface $product) {
                    return sprintf(
                        '[%s] %s',
                        $product->getReference(),
                        $product->getFullDesignation(true)
                    );
                }
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

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
