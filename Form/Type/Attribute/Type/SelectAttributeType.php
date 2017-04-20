<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\CollectionToValueTransformer;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SelectAttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SelectAttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = $options['attribute'];

        if (!$multiple = $attribute->getConfig()['multiple']) {
            $transformer = new CollectionToValueTransformer();
        } else {
            $transformer = new CollectionToArrayTransformer();
        }

        $choice = $builder
            ->create('choices', ChoiceType::class, [
                'label'        => false,
                'choices'      => $attribute->getChoices()->toArray(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple'     => $multiple,
                'required'     => $options['required'],
                'attr'         => [
                    'widget_col' => 12,
                ],
            ])
            ->addModelTransformer($transformer);

        $builder->add($choice);
    }
}
