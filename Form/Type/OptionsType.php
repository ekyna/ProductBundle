<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ProductBundle\Form\DataTransformer\OptionsToGroupsTransformer;

/**
 * OptionsType
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OptionsType extends AbstractType
{
    /**
     * @var Doctrine\ORM\EntityRepository
     */
    private $optionGroupRepository;

    /**
     * Constructor
     * 
     * @param EntityRepository $optionGroupRepository
     */
    public function __construct(EntityRepository $optionGroupRepository)
    {
        $this->optionGroupRepository = $optionGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = $this->optionGroupRepository->findBy(array(), array('position' => 'ASC'));
        foreach($options as $option) {
            $builder
                ->add($option->getId(), 'bootstrap_collection', array(
                    'label'        => $option->getName(),
                    'type'         => 'ekyna_product_option',
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'add_button_text'    => 'Ajouter une option',
                    'delete_button_text' => 'Supprimer',
                    'sub_widget_col'     => 9,
                    'button_col'         => 3,
                    'by_reference' => false,
                    'options'      => array(
                        'label' => false,
                        'attr' => array(
                            'widget_col' => 12
                        ),
                        'data_class' => 'Ekyna\Bundle\ProductBundle\Entity\Option',
                    )
                ))
            ;
        }

        $builder->addModelTransformer(new OptionsToGroupsTransformer($this->optionGroupRepository));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
    	return 'ekyna_product_options';
    }
}
