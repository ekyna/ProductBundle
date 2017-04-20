<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Inventory;

use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Ekyna\Bundle\ProductBundle\Model\InventoryContext;
use Ekyna\Bundle\ProductBundle\Model\InventoryProfiles;
use Ekyna\Bundle\ProductBundle\Repository\BrandRepository;
use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\IdentifierToResourceTransformer;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class InventoryType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryType extends AbstractType
{
    private BrandRepository             $brandRepository;
    private ResourceRepositoryInterface $supplierRepository;

    public function __construct(BrandRepository $brandRepository, ResourceRepositoryInterface $supplierRepository)
    {
        $this->brandRepository = $brandRepository;
        $this->supplierRepository = $supplierRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder
                    ->create('brand', BrandChoiceType::class, [
                        'required' => false,
                        'select2'  => false,
                    ])
                    ->addModelTransformer(new IdentifierToResourceTransformer($this->brandRepository))
            )
            ->add(
                $builder
                    ->create('supplier', SupplierChoiceType::class, [
                        'required' => false,
                        'select2'  => false,
                    ])
                    ->addModelTransformer(new IdentifierToResourceTransformer($this->supplierRepository))
            )
            ->add('designation', Type\TextType::class, [
                'label'    => t('field.designation', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('reference', Type\TextType::class, [
                'label'    => t('field.reference', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('geocode', Type\TextType::class, [
                'label'    => t('field.geocode', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('visible', Type\ChoiceType::class, [
                'label'                     => t('field.visible', [], 'EkynaUi'),
                'choices'                   => [
                    'value.yes' => 1,
                    'value.no'  => 0,
                ],
                'choice_translation_domain' => 'EkynaUi',
                'required'                  => false,
                'select2'                   => false,
            ])
            ->add('quoteOnly', Type\ChoiceType::class, [
                'label'                     => t('stock_subject.field.quote_only', [], 'EkynaCommerce'),
                'choices'                   => [
                    'value.yes' => 1,
                    'value.no'  => 0,
                ],
                'choice_translation_domain' => 'EkynaUi',
                'required'                  => false,
                'select2'                   => false,
            ])
            ->add('endOfLife', Type\ChoiceType::class, [
                'label'                     => t('stock_subject.field.end_of_life', [], 'EkynaCommerce'),
                'choices'                   => [
                    'value.yes' => 1,
                    'value.no'  => 0,
                ],
                'choice_translation_domain' => 'EkynaUi',
                'required'                  => false,
                'select2'                   => false,
            ])
            ->add('mode', ConstantChoiceType::class, [
                'label'    => t('stock_subject.field.mode', [], 'EkynaCommerce'),
                'class'    => StockSubjectModes::class,
                'required' => false,
                'select2'  => false,
            ])
            ->add('state', ConstantChoiceType::class, [
                'label'    => t('stock_subject.field.state', [], 'EkynaCommerce'),
                'class'    => StockSubjectStates::class,
                'required' => false,
                'select2'  => false,
            ])
            ->add('bookmark', Type\ChoiceType::class, [
                'label'                     => t('inventory.field.bookmark', [], 'EkynaProduct'),
                'choices'                   => [
                    'value.yes' => 1,
                    'value.no'  => 0,
                ],
                'choice_translation_domain' => 'EkynaUi',
                'required'                  => false,
                'select2'                   => false,
            ])
            ->add('profile', ConstantChoiceType::class, [
                'label'   => t('inventory.field.profile', [], 'EkynaProduct'),
                'class'   => InventoryProfiles::class,
                'select2' => false,
            ])
            ->add('sortBy', Type\HiddenType::class)
            ->add('sortDir', Type\HiddenType::class)
            ->add('submit', Type\SubmitType::class, [
                'label' => t('button.apply', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'btn-sm',
                ],
            ])
            ->add('reset', Type\ResetType::class, [
                'label' => t('button.reset', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'btn-sm',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', InventoryContext::class);
    }
}
