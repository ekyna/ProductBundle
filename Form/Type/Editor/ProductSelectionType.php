<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Editor;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\ArrayToProductEntriesTransformer;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ProductSelectionType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Editor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSelectionType extends AbstractType
{
    private ProductRepositoryInterface $repository;

    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ArrayToProductEntriesTransformer($this->repository));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'          => t('product.label.plural', [], 'EkynaProduct'),
            'allow_add'      => true,
            'allow_sort'     => true,
            'allow_delete'   => true,
            'entry_type'     => ProductEntryType::class,
            'sub_widget_col' => 10,
            'button_col'     => 2,
        ]);
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
