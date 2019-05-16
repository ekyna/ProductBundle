<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class OptionGroupType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupType extends Form\AbstractType
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
     * @param FormBuilder $formBuilder
     */
    public function __construct(ItemBuilder $itemBuilder, FormBuilder $formBuilder)
    {
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formBuilder;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        /** @var Model\OptionGroupInterface $optionGroup */
        $optionGroup = $options['option_group'];

        $required = false;
        $constraints = [];
        if ($optionGroup->isRequired()) {
            $constraints[] = new NotNull();
            $required = true;
        }

        $options = $this->itemBuilder->getFilter()->getGroupOptions($optionGroup);

        $transformer = new IdToChoiceObjectTransformer($options);

        $submitListener = function (Form\FormEvent $event) use ($options) {
            $item = $event->getForm()->getParent()->getData();

            /** @var Model\OptionInterface $option */
            if (null !== $option = $event->getData()) {
                $this->itemBuilder->buildFromOption($item, $option, count($options));
            }
        };

        $field = $builder
            ->create('choice', ChoiceType::class, [
                'label'         => false,
                'property_path' => 'data[' . ItemBuilder::OPTION_ID . ']',
                'placeholder'   => $required ? null : 'ekyna_product.sale_item_configure.choose_option',
                'required'      => $required,
                'constraints'   => $constraints,
                'select2'       => false,
                'attr'          => ['class' => 'sale-item-option'],
                'choices'       => $options,
                'choice_value'  => 'id',
                'choice_label'  => [$this->formBuilder, 'optionChoiceLabel'],
                'choice_attr'   => [$this->formBuilder, 'optionChoiceAttr'],
            ])
            ->addModelTransformer($transformer)
            ->addEventListener(Form\FormEvents::SUBMIT, $submitListener, 1024);

        $builder->add($field);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var Model\OptionGroupInterface $optionGroup */
        $optionGroup = $options['option_group'];

        $view->vars['group_id'] = $optionGroup->getId();
        $view->vars['group_position'] = $optionGroup->getPosition();

        $choices = $this->itemBuilder->getFilter()->getGroupOptions($optionGroup);

        if ($options['required'] && (1 === count($choices))) {
            $view->vars['attr']['style'] = 'display: none;';
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['option_group'])
            ->setDefaults([
                'data_class' => SaleItemInterface::class,
                'required'   => function (Options $options, /** @noinspection PhpUnusedParameterInspection */ $value) {
                    /** @var Model\OptionGroupInterface $optionGroup */
                    $optionGroup = $options['option_group'];

                    return $optionGroup->isRequired();
                },
            ])
            ->setAllowedTypes('option_group', Model\OptionGroupInterface::class);
    }
}
