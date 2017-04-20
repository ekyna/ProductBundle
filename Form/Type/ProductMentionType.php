<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MentionType;
use Ekyna\Bundle\ProductBundle\Entity\ProductMentionTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductMentionType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductMentionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_class', ProductMentionTranslation::class);
    }

    public function getParent(): ?string
    {
        return MentionType::class;
    }
}
