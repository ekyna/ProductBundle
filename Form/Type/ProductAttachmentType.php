<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AttachmentType;
use Ekyna\Bundle\ProductBundle\Model\ProductAttachmentTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ProductAttachmentType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttachmentType extends AttachmentType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('locale', LocaleChoiceType::class)
            ->add('type', ConstantChoiceType::class, [
                'label'    => t('field.type', [], 'EkynaUi'),
                'class'    => ProductAttachmentTypes::class,
                'required' => false,
            ]);
    }
}
