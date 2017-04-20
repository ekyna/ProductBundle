<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\ProductBundle\Form\EventListener\ProductTypeSubscriber;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function strlen;

/**
 * Class ProductType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends AbstractResourceType
{
    private ProductTypeSubscriber $subscriber;

    public function __construct(ProductTypeSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber($this->subscriber);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                /** @var ProductInterface $product */
                $product = $form->getData();

                if (!strlen($product->getType())) {
                    throw new RuntimeException('Product type is not set.');
                }

                return ['Default', $product->getType()];
            },
        ]);
    }
}
