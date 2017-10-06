<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class NewSupplierProductType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NewSupplierProductType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('supplier', SupplierChoiceType::class, [
                /*'constraints' => [
                    new NotNull(),
                ],*/
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'ekyna_commerce.supplier_product.button.new',
            ]);
    }
}
