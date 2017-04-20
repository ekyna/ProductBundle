<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

use function Symfony\Component\Translation\t;

/**
 * Class NewSupplierProductType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NewSupplierProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supplier', SupplierChoiceType::class, [
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => t('supplier_product.button.new', [], 'EkynaCommerce'),
            ]);
    }
}
