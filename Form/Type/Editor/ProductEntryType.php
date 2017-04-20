<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Editor;

use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductThumbEntryType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Editor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', ProductSearchType::class, [
                'label' => false,
                'attr'  => [
                    'label_col'  => 0,
                    'widget_col' => 12,
                ],
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'data-collection-role' => 'position',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductEntry::class,
        ]);
    }
}
