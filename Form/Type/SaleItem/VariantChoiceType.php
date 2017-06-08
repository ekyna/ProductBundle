<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Bundle\ProductBundle\Service\FormHelper;
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
     * @var ProductProvider
     */
    private $provider;

    /**
     * @var FormHelper
     */
    private $formHelper;


    /**
     * Constructor.
     *
     * @param ProductProvider $provider
     * @param FormHelper      $formHelper
     */
    public function __construct($provider, FormHelper $formHelper)
    {
        $this->provider = $provider;
        $this->formHelper = $formHelper;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Model\ProductInterface $variable */
        $variable = $options['variable'];

        $transformer = new IdToChoiceObjectTransformer($variable->getVariants()->toArray());

        $builder->addModelTransformer($transformer);

        // On post submit, build item from variant
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($transformer) {
            $data = $event->getData();

            $item = $event->getForm()->getParent()->getData();

            $this->provider->getItemBuilder()->buildFromVariant($item, $transformer->transform($data));
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

            return $variable->getVariants();
        };

        $resolver
            ->setDefaults([
                'label'         => 'ekyna_product.variant.label.singular',
                'property_path' => 'data[' . ItemBuilder::VARIANT_ID . ']',
                'constraints'   => [
                    new NotNull(),
                ],
                'select2'       => false,
                'attr'          => [
                    'class' => 'sale-item-variant',
                ],
                'choices'       => $choices,
                'choice_value'  => 'id',
                'choice_label'  => [$this->formHelper, 'variantChoiceLabel'],
                'choice_attr'   => [$this->formHelper, 'variantChoiceAttr'],
            ])
            ->setRequired(['variable'])
            ->setAllowedTypes('variable', Model\ProductInterface::class)
            ->setAllowedValues('variable', function (Model\ProductInterface $variable) {
                return $variable->getType() === Model\ProductTypes::TYPE_VARIABLE;
            });
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
