<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Brand;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BrandChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Brand
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $brandClass;


    /**
     * Constructor.
     *
     * @param string $brandClass
     */
    public function __construct($brandClass)
    {
        $this->brandClass = $brandClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'ekyna_product.brand.label.singular',
            'class' => $this->brandClass,
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
