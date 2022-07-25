<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Pricing;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PricingGroupType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Pricing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PricingGroupType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => t('field.name', [], 'EkynaUi'),
        ]);
    }
}
