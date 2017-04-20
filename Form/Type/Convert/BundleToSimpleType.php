<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

use function Symfony\Component\Translation\t;

/**
 * Class BundleToSimpleType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleToSimpleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => t('convert.bundle_to_simple.confirm', [], 'EkynaProduct'),
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
                            'label'        => t('button.save', [], 'EkynaUi'),
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

    public function configureOptions(OptionsResolver $resolver): void
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
     */
    private function addBundleForm(FormInterface $form, ProductInterface $bundle): void
    {
        if (empty($optionGroups = $this->getOptionsGroups($bundle))) {
            return;
        }

        $form->add('option_group_selection', OptionGroupChoiceType::class, [
            'optionGroups' => $optionGroups,
            'attr'         => [
                'help_text' => t('convert.bundle_to_simple.option_group_choice', [], 'EkynaProduct'),
            ],
        ]);
    }

    /**
     * Returns the bundle choices products options groups.
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
