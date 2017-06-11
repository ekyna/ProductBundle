<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Bundle\ProductBundle\Service\FormHelper;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class ConfigurableSlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableSlotType extends Form\AbstractType
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
    public function __construct(
        ProductProvider $provider,
        FormHelper $formHelper
    ) {
        $this->provider = $provider;
        $this->formHelper = $formHelper;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        /** @var Model\BundleSlotInterface $bundleSlot */
        $bundleSlot = $options['bundle_slot'];

        $choices = $bundleSlot->getChoices()->toArray();

        $transformer = new IdToChoiceObjectTransformer($choices);

        $postSubmitListener = function (Form\FormEvent $event) use ($transformer) {
            $item = $event->getForm()->getParent()->getData();
            $item->getSubjectIdentity()->clear();

            /** @var int $data */
            $data = $event->getData();

            /** @var Model\BundleChoiceInterface $choice */
            if (null !== $choice = $transformer->transform($data)) {
                $this->provider->assign($item, $choice->getProduct());
            }
        };

        $choiceLabel = function (Model\BundleChoiceInterface $choice) {
            return $choice->getProduct()->getFullDesignation(true);
        };

        $field = $builder
            ->create('choice', Type\ChoiceType::class, [
                'label'         => false,
                'property_path' => 'data[' . ItemBuilder::BUNDLE_CHOICE_ID . ']',
                'placeholder'   => 'ekyna_product.sale_item_configure.choose_option',
                'constraints'   => [new NotNull()],
                'select2'       => false,
                'expanded'      => true,
                'attr'          => ['class' => 'sale-item-bundle-choice'],
                'choices'       => $choices,
                'choice_value'  => 'id',
                'choice_label'  => $choiceLabel,
            ])
            ->addModelTransformer($transformer)
            ->addEventListener(Form\FormEvents::POST_SUBMIT, $postSubmitListener, 1024);

        $formBuilder = $this->provider->getFormBuilder();

        $buildForm = function (Form\FormEvent $event) use ($transformer, $formBuilder) {
            $form = $event->getForm();
            $choiceId = $form->get('choice')->getData();

            /** @var Model\BundleChoiceInterface $choice */
            $choice = $transformer->transform($choiceId);

            $formBuilder->buildBundleChoiceForm($form, $choice);
        };

        $builder
            ->add($field)
            ->addEventListener(Form\FormEvents::POST_SET_DATA, $buildForm)
            ->addEventListener(Form\FormEvents::PRE_SUBMIT, $buildForm)
            ->addEventListener(Form\FormEvents::POST_SUBMIT, function (Form\FormEvent $event) use ($transformer) {
                /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
                $item = $event->getData();
                $choiceId = $event->getForm()->get('choice')->getData();

                /** @var Model\BundleChoiceInterface $choice */
                $choice = $transformer->transform($choiceId);

                $this->provider->getItemBuilder()->buildFromBundleChoice($item, $choice);
            });
    }

    /**
     * @inheritDoc
     */
    public function finishView(Form\FormView $view, Form\FormInterface $form, array $options)
    {
        /** @var SaleItemInterface $item */
        $item = $form->getData();

        /** @var Model\BundleSlotInterface $bundleSlot */
        $bundleSlot = $options['bundle_slot'];
        /** @var Model\BundleChoiceInterface[] $bundleChoices */
        $bundleChoices = $bundleSlot->getChoices()->toArray();
        $transformer = new IdToChoiceObjectTransformer($bundleChoices);

        // Add image to each subject choice radio buttons vars
        foreach ($view->children['choice']->children as $subjectChoiceView) {
            /** @var Model\BundleChoiceInterface $bundleChoice */
            $bundleChoice = $transformer->transform($subjectChoiceView->vars['value']);
            $product = $bundleChoice->getProduct();
            $path = $this->formHelper->getProductImagePath($product, 'slot_choice_btn');
            $subjectChoiceView->vars['choice_image'] = $path;
            $subjectChoiceView->vars['choice_brand'] = $product->getBrand()->getTitle();
            $subjectChoiceView->vars['choice_product'] = $product->getFullTitle();
        }

        // Builds each slot choice's form
        $formFactory = $form->getConfig()->getFormFactory();
        $formBuilder = $this->provider->getFormBuilder();

        $choiceId = $form->get('choice')->getData();
        $choicesForms = [];

        foreach ($bundleChoices as $bundleChoice) {
            if ($bundleChoice->getId() == $choiceId) {
                $this->addChoiceVars($view, $bundleChoice);
                $this->addPricingVars($view, $item, !$options['admin_mode']);
            } else {
                $choiceForm = $formFactory->createNamed('BUNDLE_CHOICE_NAME', BundleSlotChoiceType::class, null, [
                    'id'         => $view->vars['id'] . '_choice_' . $bundleChoice->getId(),
                    'data_class' => SaleItemInterface::class,
                ]);

                $formBuilder->buildBundleChoiceForm($choiceForm, $bundleChoice);

                // Create a fake item
                $fakeItem = $item->createChild();

                $this->provider->assign($fakeItem, $bundleChoice->getProduct());
                $choiceForm->setData($fakeItem);

                $choiceFormView = $choiceForm->createView();
                $this->addChoiceVars($choiceFormView, $bundleChoice);
                $this->addPricingVars($choiceFormView, $fakeItem, !$options['admin_mode']);

                // Remove the fake item
                $item->removeChild($fakeItem);

                $choicesForms[] = $choiceFormView;
            }
        }

        $view->vars['slot_title'] = $bundleSlot->getTitle();
        $view->vars['slot_description'] = $bundleSlot->getDescription();
        $view->vars['choices_forms'] = $choicesForms;
    }

    /**
     * Adds the bundle choice vars to the view.
     *
     * @param Form\FormView               $view
     * @param Model\BundleChoiceInterface $bundleChoice
     */
    private function addChoiceVars(Form\FormView $view, Model\BundleChoiceInterface $bundleChoice)
    {
        $product = $bundleChoice->getProduct();

        $view->vars['choice_id'] = $bundleChoice->getId();
        $view->vars['choice_brand'] = $product->getBrand()->getTitle();
        $view->vars['choice_product'] = $product->getFullTitle();
        $view->vars['choice_description'] = $product->getDescription();
        $view->vars['choice_image'] = $this->formHelper->getProductImagePath($product);
    }

    /**
     * Adds the pricing vars to the view.
     *
     * @param Form\FormView     $view
     * @param SaleItemInterface $item
     * @param bool              $fallback
     */
    private function addPricingVars(Form\FormView $view, SaleItemInterface $item, $fallback)
    {
        $config = $this->formHelper->getPricingConfig($item, $fallback);

        $view->vars['pricing'] = $config;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'      => false,
                'data_class' => SaleItemInterface::class,
            ])
            ->setRequired(['bundle_slot'])
            ->setAllowedTypes('bundle_slot', Model\BundleSlotInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'sale_item_configurable_slot';
    }
}
