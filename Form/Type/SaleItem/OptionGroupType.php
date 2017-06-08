<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Bundle\ProductBundle\Service\FormHelper;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
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
     * @var ResourceRepositoryInterface
     */
    private $optionRepository;

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
     * @param ResourceRepositoryInterface $optionRepository
     * @param ProductProvider             $provider
     * @param FormHelper                  $formHelper
     */
    public function __construct(
        ResourceRepositoryInterface $optionRepository,
        ProductProvider $provider,
        FormHelper $formHelper
    ) {
        $this->optionRepository = $optionRepository;
        $this->provider = $provider;
        $this->formHelper = $formHelper;
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

        $choices = $optionGroup->getOptions()->toArray();

        $transformer = new IdToChoiceObjectTransformer($choices);

        $postSubmitListener = function (Form\FormEvent $event) use ($optionGroup, $transformer) {
            $item = $event->getForm()->getParent()->getData();
            /** @var int $data */
            $data = $event->getData();

            if (null !== $option = $transformer->transform($data)) {
                /** @var Model\OptionInterface $option */
                $this
                    ->provider
                    ->getItemBuilder()
                    ->buildFromOption($item, $option);
            }
        };

        $options = $builder
            ->create('choice', ChoiceType::class, [
                'label'         => false,
                'property_path' => 'data[' . ItemBuilder::OPTION_ID . ']',
                'placeholder'   => 'ekyna_product.sale_item_configure.choose_option',
                'required'      => $required,
                'constraints'   => $constraints,
                'select2'       => false,
                'attr'          => [
                    'class' => 'sale-item-option',
                    'group_id'
                ],
                'choices'       => $choices,
                'choice_value'  => 'id',
                'choice_label'  => [$this->formHelper, 'optionChoiceLabel'],
                'choice_attr'   => [$this->formHelper, 'optionChoiceAttr'],
            ])
            ->addModelTransformer($transformer)
            ->addEventListener(Form\FormEvents::POST_SUBMIT, $postSubmitListener, 1024);

        $builder->add($options);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var Model\OptionGroupInterface $optionGroup */
        $optionGroup = $options['option_group'];

        $view->vars['group_id'] = $optionGroup->getId();
        $view->vars['group_type'] = $optionGroup->getProduct()->getType();
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        /** @noinspection PhpUnusedParameterInspection */
        $resolver
            ->setDefaults([
                'data_class' => SaleItemInterface::class,
                'required' => function(Options $options, $value) {
                    /** @var Model\OptionGroupInterface $optionGroup */
                    $optionGroup = $options['option_group'];
                    return $optionGroup->isRequired();
                }
            ])
            ->setRequired(['option_group'])
            ->setAllowedTypes('option_group', Model\OptionGroupInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_sale_item_option_group';
    }
}
