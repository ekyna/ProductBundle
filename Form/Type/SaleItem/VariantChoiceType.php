<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class VariantChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantChoiceType extends AbstractType
{
    /**
     * @var ItemBuilder
     */
    private $itemBuilder;

    /**
     * @var FormBuilder
     */
    private $formBuilder;


    /**
     * Constructor.
     *
     * @param ItemBuilder $itemBuilder
     * @param FormBuilder $formHelper
     */
    public function __construct(ItemBuilder $itemBuilder, FormBuilder $formHelper)
    {
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formHelper;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Model\ProductInterface $variable */
        $variable = $options['variable'];

        $variants = $this->itemBuilder->getFilter()->getVariants($variable);

        $transformer = new IdToChoiceObjectTransformer($variants);

        $builder->addModelTransformer($transformer);

        // On post submit, build item from variant
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $item = $event->getForm()->getParent()->getData();

            /** @var Model\ProductInterface $variant */
            $variant = $event->getData();

            $this->itemBuilder->buildFromVariant($item, $variant);
        });
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-parent'] = $view->parent->vars['full_name'];
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = function (Options $options, $value) {
            /** @var Model\ProductInterface $variable */
            $variable = $options['variable'];

            return $this->itemBuilder->getFilter()->getVariants($variable);
        };

        $resolver
            ->setDefaults([
                'label'           => 'ekyna_product.sale_item_configure.variant',
                'property_path'   => 'data[' . ItemBuilder::VARIANT_ID . ']',
                'constraints'     => [
                    new NotNull(),
                ],
                'select2'         => false,
                'attr'            => [
                    'class' => 'sale-item-variant',
                ],
                'root_item'       => true,
                'exclude_options' => [],
                'choices'         => $choices,
                'choice_value'    => 'id',
                'choice_label'    => function (Model\ProductInterface $variant) {
                    return $this->formBuilder->variantChoiceLabel($variant);
                },
                'choice_attr'     => function (Options $options, $value) {
                    if ($value) {
                        return $value;
                    }

                    $root = $options['root_item'];
                    $exclude = $options['exclude_options'];

                    return function (Model\ProductInterface $variant) use ($root, $exclude) {
                        return $this->formBuilder->variantChoiceAttr($variant, $root, $exclude);
                    };
                },
            ])
            ->setRequired(['variable'])
            ->setAllowedTypes('variable', Model\ProductInterface::class)
            ->setAllowedTypes('root_item', 'bool')
            ->setAllowedTypes('exclude_options', 'array')
            ->setAllowedValues('variable', function (Model\ProductInterface $variable) {
                return $variable->getType() === Model\ProductTypes::TYPE_VARIABLE;
            });
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'sale_item_variant';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
