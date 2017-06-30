<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Ekyna\Bundle\ProductBundle\Model\InventorySearch;
use Ekyna\Bundle\ProductBundle\Repository\BrandRepository;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Form\IdToObjectTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InventorySearchType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventorySearchType extends AbstractType
{
    /**
     * @var BrandRepository
     */
    private $brandRepository;


    /**
     * Constructor.
     *
     * @param BrandRepository $brandRepository
     */
    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
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
                        'select2' => false,
                    ])
                    ->addModelTransformer(new IdToObjectTransformer($this->brandRepository))
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
                'select2' => false,
            ])
            ->add('state', Type\ChoiceType::class, [
                'label'    => 'ekyna_commerce.stock_subject.field.state',
                'choices'  => StockSubjectStates::getChoices(),
                'required' => false,
                'select2' => false,
            ])
            ->add('sortBy', Type\HiddenType::class)
            ->add('sortDir', Type\HiddenType::class)
            ->add('submit', Type\SubmitType::class, [
                'label' => 'ekyna_core.button.filter',
                'attr' => [
                    'class' => 'btn-sm'
                ]
            ])
            ->add('reset', Type\ResetType::class, [
                'label' => 'ekyna_core.button.reset',
                'attr' => [
                    'class' => 'btn-sm'
                ]
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', InventorySearch::class);
    }
}
