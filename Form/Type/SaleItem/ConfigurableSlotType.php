<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem\ConfigurableSlotListener;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
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
     * @inheritdoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        /** @var Model\BundleSlotInterface $bundleSlot */
        $bundleSlot = $options['bundle_slot'];

        $bundleChoices = $this->itemBuilder->getFilter()->getSlotChoices($bundleSlot);

        $transformer = new IdToChoiceObjectTransformer($bundleChoices);

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
                'choices'       => $bundleChoices,
                'choice_value'  => 'id',
                'choice_label'  => $choiceLabel,
            ])
            ->addModelTransformer($transformer);

        $builder
            ->add($field)
            ->addEventSubscriber(new ConfigurableSlotListener(
                $this->itemBuilder,
                $this->formBuilder,
                $transformer
            ));
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
        $bundleChoices = $this->itemBuilder->getFilter()->getSlotChoices($bundleSlot);

        $transformer = new IdToChoiceObjectTransformer($bundleChoices);

        // Add image to each subject choice radio buttons vars
        foreach ($view->children['choice']->children as $subjectChoiceView) {
            /** @var Model\BundleChoiceInterface $bundleChoice */
            $bundleChoice = $transformer->transform($subjectChoiceView->vars['value']);
            $product = $bundleChoice->getProduct();
            $path = $this->formBuilder->getProductImagePath($product, 'slot_choice_btn');
            $subjectChoiceView->vars['choice_image'] = $path;
            $subjectChoiceView->vars['choice_brand'] = $product->getBrand()->getTitle();
            $subjectChoiceView->vars['choice_product'] = $product->getFullTitle();
        }

        // Builds each slot choice's form
        $formFactory = $form->getConfig()->getFormFactory();

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

                $this->formBuilder->buildBundleChoiceForm($choiceForm, $bundleChoice);

                // Create a fake item
                $fakeItem = $item->createChild();

                $this->formBuilder->getProvider()->assign($fakeItem, $bundleChoice->getProduct());
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
        $view->vars['choice_thumb'] = $this->formBuilder->getProductImagePath($product);
        $view->vars['choice_image'] = $this->formBuilder->getProductImagePath($product, 'media_front');
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
        $config = $this->formBuilder->getPricingConfig($item, $fallback);

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
