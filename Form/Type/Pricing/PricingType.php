<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PricingType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $groupClass;

    /**
     * @var string
     */
    protected $countryClass;

    /**
     * @var string
     */
    protected $brandClass;


    /**
     * Constructor.
     *
     * @param string $pricingClass
     * @param string $groupClass
     * @param string $countryClass
     * @param string $brandClass
     */
    public function __construct($pricingClass, $groupClass, $countryClass, $brandClass)
    {
        parent::__construct($pricingClass);

        $this->groupClass = $groupClass;
        $this->countryClass = $countryClass;
        $this->brandClass = $brandClass;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('groups', ResourceType::class, [
                'label'    => 'ekyna_commerce.customer_group.label.plural',
                'class'    => $this->groupClass,
                'multiple' => true,
            ])
            ->add('countries', ResourceType::class, [
                'label'    => 'ekyna_product.pricing.field.countries',
                'class'    => $this->countryClass,
                'multiple' => true,
            ])
            ->add('brands', ResourceType::class, [
                'label'    => 'ekyna_product.brand.label.plural',
                'class'    => $this->brandClass,
                'multiple' => true,
            ])
            ->add('rules', CollectionType::class, [
                'label'         => 'ekyna_product.pricing.field.rules',
                'entry_type'    => PricingRuleType::class,
                'entry_options' => [],
                'allow_add'     => true,
                'allow_delete'  => true,
            ]);
    }
}
