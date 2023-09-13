<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Category;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function str_repeat;

/**
 * Class CategoryChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Brand
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'resource'      => 'ekyna_product.category',
            'query_builder' => function (EntityRepository $er) {
                $qb = $er->createQueryBuilder('c');

                return $qb->addOrderBy('c.left', 'ASC');
            },
            'choice_label'  => static fn($choice): string => str_repeat(
                    '&nbsp;&bull;&nbsp;',
                    $choice->getLevel()
                ) . $choice,
        ]);
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
