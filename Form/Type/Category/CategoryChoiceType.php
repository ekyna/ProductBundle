<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Category;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CategoryChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Category
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $categoryClass;


    /**
     * Constructor.
     *
     * @param string $categoryClass
     */
    public function __construct($categoryClass)
    {
        $this->categoryClass = $categoryClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'ekyna_product.category.label.singular',
            'class' => $this->categoryClass,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
