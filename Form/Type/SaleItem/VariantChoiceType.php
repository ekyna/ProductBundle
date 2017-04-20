<?php

declare(strict_types=1);

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

use function Symfony\Component\Translation\t;

/**
 * Class VariantChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantChoiceType extends AbstractType
{
    private ItemBuilder $itemBuilder;
    private FormBuilder $formBuilder;

    public function __construct(ItemBuilder $itemBuilder, FormBuilder $formHelper)
    {
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
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

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['data-parent'] = $view->parent->vars['full_name'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = function (Options $options, $value) {
            /** @var Model\ProductInterface $variable */
            $variable = $options['variable'];

            return $this->itemBuilder->getFilter()->getVariants($variable);
        };

        $resolver
            ->setDefaults([
                'label'           => t('sale_item_configure.variant', [], 'EkynaProduct'),
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

    public function getBlockPrefix(): string
    {
        return 'sale_item_variant';
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
