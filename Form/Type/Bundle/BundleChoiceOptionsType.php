<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\BundleChoiceOptionsTransformer;
use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type\AbstractType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleChoiceOptionsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceOptionsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new BundleChoiceOptionsTransformer(array_values($options['choices'])));
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-name'] = $view->vars['full_name'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('product')
            ->setAllowedTypes('product', [ProductInterface::class, 'null'])
            ->setDefaults([
                'label'    => 'ekyna_product.option_group.label.plural',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'attr'     => [
                    'inline' => true,
                ],
                'choices'  => function (Options $options, $value) {
                    if ($value) {
                        return $value;
                    }

                    /** @var ProductInterface $product */
                    if (null === $product = $options['product']) {
                        return [];
                    }

                    $choices = [];

                    foreach ($product->resolveOptionGroups([], true) as $group) {
                        $label = sprintf(
                            "[%s] %s",
                            $group->isRequired() ? 'Required' : 'Optional',
                            $group->getName()
                        );
                        $choices[$label] = $group->getId();
                    }

                    return $choices;
                },
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
