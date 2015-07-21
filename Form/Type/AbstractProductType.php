<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ekyna\Component\Sale\Product\ProductTypes;

/**
 * Class AbstractProductType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
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
     * @param array $productsConfiguration
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
                'choices' => ProductTypes::getChoices(),
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
            ->add('tax', 'ekyna_resource', array(
                'label' => 'ekyna_core.field.tax',
                'class' => 'Ekyna\Bundle\OrderBundle\Entity\Tax',
                'property' => 'name',
                'allow_new' => $options['admin_mode'],
                'empty_value' => 'ekyna_core.field.tax',
                'attr' => array(
            	    'placeholder' => 'ekyna_core.field.tax',
                ),
            ))
            ->add('options', 'ekyna_product_options', array(
                'label'   => 'ekyna_core.field.options',
                'options' => $optionGroups,
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
