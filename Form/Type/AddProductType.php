<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class AddProductType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AddProductType extends AbstractType
{
    /**
     * @var array
     */
    protected $optionsConfiguration;

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
        // TODO ProductSelection object

        $builder
            ->add('product', 'ekyna_hidden_entity', array(
                'class' => 'EkynaProductBundle:AbstractProduct',
            ))
            ->add('quantity', 'integer', array('attr' => array('min' => 1)))
            ->add('submit', 'submit', array(
        	    'label' => 'Ajouter au panier'
            ))
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event){
            $data = $event->getData();
            $form = $event->getForm();

            /** @var \Ekyna\Component\Sale\Product\ProductInterface $product */
            $product = $data['product'];

            if ($product->hasOptions()) {
                $groups = $product->getOptionsGroups();
                foreach ($groups as $group) {
                    if(! array_key_exists($group, $this->optionsConfiguration)) {
                        throw new \RuntimeException(sprintf('Undefined option configuration "%s".', $group));
                    }
                    //$config = $this->optionsConfiguration[$group];
                    $form->add('option-'.$group, 'entity', array(
                        'label' => $this->optionsConfiguration[$group]['label'],
                        'required' => false,
                        'empty_value' => 'Choisissez une option',
                        'attr' => array(
                    	    'placeholder' => 'Choisissez une option',
                        ),
                        'class' => $this->optionsConfiguration[$group]['class'],
                        'query_builder' => function(EntityRepository $er) use ($product, $group) {
                            $qb = $er->createQueryBuilder('o');
                            return $qb
                                ->andWhere($qb->expr()->eq('o.product', ':product'))
                                ->andWhere($qb->expr()->eq('o.group', ':group'))
                                ->setParameter('product', $product)
                                ->setParameter('group', $group)
                            ;
                        },
                    )); 
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
    	return 'ekyna_product_add_to_order';
    }
}
