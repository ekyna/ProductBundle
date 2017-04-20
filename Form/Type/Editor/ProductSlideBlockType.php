<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Editor;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\Translation\t;

/**
 * Class ProductSlideBlockType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Editor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSlideBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('max_width', Type\TextType::class, [
                'label'       => t('block.field.max_width', [], 'EkynaCms'),
                'required'    => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\d+(px|%)$/'
                        // TODO message translation
                    ]),
                ],
            ])
            ->add('duration', Type\IntegerType::class, [
                'label'       => t('block.field.duration', [], 'EkynaCms'),
                'required'    => false,
                'constraints' => [
                    new Assert\Range([
                        'min' => 1000,
                        'max' => 20000,
                    ]),
                ],
            ])
            ->add('product_ids', ProductSelectionType::class);
    }
}
