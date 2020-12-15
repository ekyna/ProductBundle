<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Class BundleToSimpleType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleToSimpleType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => 'ekyna_product.convert.bundle_to_simple.confirm',
                'attr'        => ['align_with_widget' => true],
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new Constraints\IsTrue(),
                ],
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'save' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => 'ekyna_core.button.save',
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                ],
            ]);

        // Post set data
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var ProductInterface $data */
            $data = $event->getData();

            $this->addBundleForm($event->getForm(), $data);
        });
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductInterface::class,
                'attr'       => [
                    'class' => 'form-horizontal',
                ],
            ]);
    }

    /**
     * Builds the bundle form.
     *
     * @param FormInterface    $form
     * @param ProductInterface $bundle
     */
    private function addBundleForm(FormInterface $form, ProductInterface $bundle): void
    {
        if (empty($optionGroups = $this->getOptionsGroups($bundle))) {
            return;
        }

        $form->add('option_group_selection', OptionGroupChoiceType::class, [
            'optionGroups' => $optionGroups,
            'attr'         => [
                'help_text' => 'ekyna_product.convert.bundle_to_simple.option_group_choice',
            ],
        ]);
    }

    /**
     * Returns the bundle choices products options groups.
     *
     * @param ProductInterface $bundle
     *
     * @return array
     */
    private function getOptionsGroups(ProductInterface $bundle): array
    {
        $groups = [];

        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $child = $choice->getProduct();
            $excluded = $choice->getExcludedOptionGroups();

            foreach ($child->getOptionGroups() as $group) {
                if (in_array($group->getId(), $excluded, true)) {
                    continue;
                }

                $groups[] = $group;
            }
        }

        return $groups;
    }
}
