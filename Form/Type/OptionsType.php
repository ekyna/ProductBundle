<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Ekyna\Bundle\ProductBundle\Form\DataTransformer\OptionsToGroupsTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OptionsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OptionsType extends AbstractType
{
    /**
     * The options configuration.
     * 
     * @var array
     */
    private $optionsConfiguration;

    /**
     * Constructor.
     * 
     * @param array $optionsConfiguration
     */
    public function __construct(array $optionsConfiguration)
    {
        $this->optionsConfiguration = $optionsConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach($this->optionsConfiguration as $name => $option) {
            if (! in_array($name, $options['options'])) {
                continue;
            }
            $builder
                ->add($name, 'ekyna_collection', [
                    'label'           => $option['label'],
                    'type'            => $option['form_type'],
                    'allow_add'       => true,
                    'allow_delete'    => true,
                    'add_button_text' => 'Ajouter une option',
                    'sub_widget_col'  => 9,
                    'button_col'      => 3,
                    'options'         => [
                        'label' => false,
                        'attr'  => [
                            'widget_col' => 12
                        ],
                        'data_class' => $option['class'],
                    ]
                ])
            ;
        }

        $builder->addModelTransformer(new OptionsToGroupsTransformer($this->optionsConfiguration));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'options' => null,
            ])
            ->setRequired(['options'])
            ->setAllowedTypes('options', 'array')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
    	return 'ekyna_product_options';
    }
}
