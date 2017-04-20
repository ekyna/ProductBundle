<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Brand;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BrandChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Brand
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'resource'      => 'ekyna_product.brand',
            'query_builder' => function (EntityRepository $er) {
                $qb = $er->createQueryBuilder('b');

                return $qb->addOrderBy('b.name', 'ASC');
            },
        ]);
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
