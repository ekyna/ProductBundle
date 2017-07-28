<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Inventory;

use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Ekyna\Bundle\ProductBundle\Model\InventoryContext;
use Ekyna\Bundle\ProductBundle\Model\InventoryProfiles;
use Ekyna\Bundle\ProductBundle\Repository\BrandRepository;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Form\IdToObjectTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InventoryType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryType extends AbstractType
{
    /**
     * @var BrandRepository
     */
    private $brandRepository;

    /**
     * @var ResourceRepository
     */
    private $supplierRepository;


    /**
     * Constructor.
     *
     * @param BrandRepository    $brandRepository
     * @param ResourceRepository $supplierRepository
     */
    public function __construct(BrandRepository $brandRepository, ResourceRepository $supplierRepository)
    {
        $this->brandRepository = $brandRepository;
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                $builder
                    ->create('brand', BrandChoiceType::class, [
                        'required' => false,
                        'select2'  => false,
                    ])
                    ->addModelTransformer(new IdToObjectTransformer($this->brandRepository))
            )
            ->add(
                $builder
                    ->create('supplier', SupplierChoiceType::class, [
                        'required' => false,
                        'select2'  => false,
                    ])
                    ->addModelTransformer(new IdToObjectTransformer($this->supplierRepository))
            )
            ->add('designation', Type\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'required' => false,
            ])
            ->add('reference', Type\TextType::class, [
                'label'    => 'ekyna_core.field.reference',
                'required' => false,
            ])
            ->add('geocode', Type\TextType::class, [
                'label'    => 'ekyna_product.product.field.geocode',
                'required' => false,
            ])
            ->add('mode', Type\ChoiceType::class, [
                'label'    => 'ekyna_commerce.stock_subject.field.mode',
                'choices'  => StockSubjectModes::getChoices(),
                'required' => false,
                'select2'  => false,
            ])
            ->add('state', Type\ChoiceType::class, [
                'label'    => 'ekyna_commerce.stock_subject.field.state',
                'choices'  => StockSubjectStates::getChoices(),
                'required' => false,
                'select2'  => false,
            ])
            ->add('profile', Type\ChoiceType::class, [
                'label'    => 'ekyna_product.inventory.field.profile',
                'choices'  => InventoryProfiles::getChoices(),
                'select2'  => false,
            ])
            ->add('sortBy', Type\HiddenType::class)
            ->add('sortDir', Type\HiddenType::class)
            ->add('submit', Type\SubmitType::class, [
                'label' => 'ekyna_core.button.apply',
                'attr'  => [
                    'class' => 'btn-sm',
                ],
            ])
            ->add('reset', Type\ResetType::class, [
                'label' => 'ekyna_core.button.reset',
                'attr'  => [
                    'class' => 'btn-sm',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', InventoryContext::class);
    }
}
