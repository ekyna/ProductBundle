<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
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
     * Constructor.
     *
     * @param ProductTypeSubscriber $subscriber
     * @param string                $productClass
     */
    public function __construct(ProductTypeSubscriber $subscriber, $productClass)
    {
        parent::__construct($productClass);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
