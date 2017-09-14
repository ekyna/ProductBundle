<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\CmsBundle\Model\TagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TagChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TagChoiceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'    => 'ekyna_cms.tag.label.plural',
                'mapped'   => false,
                'tags'     => [],
                'choices'  => function (Options $options, $value) {
                    if (empty($value)) {
                        $tags = $options['tags'];
                        /** @var TagInterface $tag */
                        foreach ($tags as $tag) {
                            $value[(string)$tag] = $tag->getId();
                        }
                    }

                    return $value;
                },
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.convert.help.tag_choice',
                ],
            ])
            ->setAllowedTypes('tags', 'array')
            ->setAllowedValues('tags', function ($value) {
                if (empty($value)) {
                    return false;
                }

                foreach ($value as $tag) {
                    if (!$tag instanceof TagInterface) {
                        return false;
                    }
                }

                return true;
            });
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
