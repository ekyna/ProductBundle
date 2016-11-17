<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaCollectionType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Form\EventListener\ProductTypeSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends ResourceFormType
{
    /**
     * @var ProductTypeSubscriber
     */
    protected $subscriber;

    /**
     * @var string
     */
    protected $imageClass;


    /**
     * Constructor.
     *
     * @param ProductTypeSubscriber $subscriber
     * @param string                $productClass
     * @param string                $imageClass
     */
    public function __construct(ProductTypeSubscriber $subscriber, $productClass, $imageClass)
    {
        parent::__construct($productClass);

        $this->subscriber = $subscriber;
        $this->imageClass = $imageClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // TODO not if variant ????
            ->add('brand', BrandChoiceType::class, [
                'allow_new' => true,
                'required'  => true,
            ])
            ->add('category', CategoryChoiceType::class, [
                'allow_new' => true,
                'required'  => true,
            ])
            ->add('images', MediaCollectionType::class, [
                'label'       => 'ekyna_core.field.images',
                'media_class' => $this->imageClass,
                'types'       => [MediaTypes::IMAGE],
                'required'    => false,
            ]);

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
                $product = $form->getData();

                if (!strlen($type = $product->getType())) {
                    throw new \RuntimeException('Product type is not set.');
                }

                return ['Default', $product->getType()];
            },
        ]);
    }
}
