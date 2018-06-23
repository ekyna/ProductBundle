<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Editor;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductSlideBlockType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Editor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSlideBlockType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('max_width', Type\TextType::class, [
                'label'       => 'ekyna_cms.block.field.max_width',
                'required'    => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\d+(px|%)$/'
                        // TODO message translation
                    ]),
                ],
            ])
            ->add('duration', Type\IntegerType::class, [
                'label'    => 'ekyna_cms.block.field.duration',
                'required' => false,
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
