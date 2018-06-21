<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Editor;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\ProductBundle\Form\DataTransformer\ArrayToProductEntriesTransformer;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ProductSlideBlockType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Editor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSlideBlockType extends AbstractType
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
        $products = $builder
            ->create('product_ids', CollectionType::class, [
                'label'          => false,
                'allow_add'      => true,
                'allow_sort'     => true,
                'allow_delete'   => true,
                'entry_type'     => ProductEntryType::class,
                'sub_widget_col' => 10,
                'button_col'     => 2,
                'attr'           => [
                    'label_col'  => 0,
                    'widget_col' => 12,
                ],
            ])
            ->addModelTransformer(new ArrayToProductEntriesTransformer($this->repository));

        $builder->add($products);
    }
}
