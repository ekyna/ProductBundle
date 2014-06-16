<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ekyna\Component\Sale\Product\ProductTypes;

/**
 * AbstractProductType.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractProductType extends AbstractType
{
    /**
     * The product class.
     * 
     * @var string
     */
    protected $dataClass;

    /**
     * The products configuration.
     *
     * @var array
     */
    protected $productsConfiguration;

    /**
     * Constructor.
     * 
     * @param string $class
     */
    public function __construct($class, array $productsConfiguration)
    {
        $this->dataClass = $class;
        $this->productsConfiguration = $productsConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $optionGroups = array();
        foreach($this->productsConfiguration as $config) {
            if($config['class'] === $options['data_class']) {
                $optionGroups = $config['options'];
            }
        }
        
        $builder
            ->add('designation', 'text', array(
                'label' => 'ekyna_core.field.designation',
            ))
            ->add('reference', 'text', array(
                'label' => 'ekyna_core.field.reference',
            ))
            ->add('type', 'choice', array(
                'label' => 'ekyna_core.field.type',
                'choices' => array(
            	    ProductTypes::PHYSICAL     => 'ekyna_product.type.' . ProductTypes::PHYSICAL,
            	    ProductTypes::VIRTUAL      => 'ekyna_product.type.' . ProductTypes::VIRTUAL,
            	    ProductTypes::DOWNLOADABLE => 'ekyna_product.type.' . ProductTypes::DOWNLOADABLE,
                )
            ))
            ->add('weight', 'integer', array(
                'label' => 'ekyna_core.field.weight',
                'attr' => array('input_group' => array('append' => 'g'), 'min' => 0),
            ))
            ->add('price', 'number', array(
                'label' => 'ekyna_core.field.price',
                'precision' => 5,
                'attr' => array('input_group' => array('append' => '€')),
            ))
            ->add('tax', 'entity', array(
                'label' => 'ekyna_core.field.tax',
                'class' => 'Ekyna\Bundle\ProductBundle\Entity\Tax',
                'multiple' => false,
                'property' => 'name',
                'add_route' => $options['admin_mode'] ? 'ekyna_product_tax_admin_new' : false,
                'empty_value' => 'ekyna_core.field.tax',
                'attr' => array(
            	    'placeholder' => 'ekyna_core.field.tax',
                ),
            ))
            ->add('options', 'ekyna_product_options', array(
                'label'   => 'ekyna_core.field.options',
                'options' => $optionGroups
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }
}
