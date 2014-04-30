<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * CategoryType.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryType extends AbstractType
{
    protected $dataClass;

    public function __construct($class)
    {
        $this->dataClass = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'ekyna_core.field.name',
                'required' => true,
            ))
            ->add('parent', 'entity', array(
                'label' => 'ekyna_core.field.parent',
                'class' => $this->dataClass,
                'empty_value' => 'ekyna_core.field.root',
                'query_builder' => function(EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('p')
                        ->orderBy('p.left', 'ASC')
                    ;
                },
                'property' => 'name',
                'required' => false,
            ))
            ->add('image', 'ekyna_core_image', array(
                'label' => 'ekyna_core.field.image',
                'data_class' => 'Ekyna\Bundle\ProductBundle\Entity\CategoryImage',
                'required' => false
            ))
            ->add('seo', 'ekyna_cms_seo', array(
                'label' => false
            ))
        ;

        //if(!$this->contentEnabled) {
            $builder
                ->add('html', 'textarea', array(
                    'label' => 'ekyna_core.field.content',
                    'attr' => array(
                	    'class' => 'tinymce',
                        'data-theme' => 'advanced',
                    )
                ))
            ;
        //}
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
    	return 'ekyna_product_category';
    }
}
