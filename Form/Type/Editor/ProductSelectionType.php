<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Editor;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\ProductBundle\Form\DataTransformer\ArrayToProductEntriesTransformer;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductSelectionType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Editor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSelectionType extends AbstractType
{
    /**
     * @var ProductRepositoryInterface
     */
    private $repository;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ArrayToProductEntriesTransformer($this->repository));
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'          => 'ekyna_product.product.label.plural',
            'allow_add'      => true,
            'allow_sort'     => true,
            'allow_delete'   => true,
            'entry_type'     => ProductEntryType::class,
            'sub_widget_col' => 10,
            'button_col'     => 2,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}