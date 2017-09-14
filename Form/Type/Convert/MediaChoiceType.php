<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MediaChoiceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $medias = $options['medias'];
        $children = $view->children;

        foreach ($children as $child) {
            $child->vars['media'] = null;

            /** @var ProductMediaInterface $media */
            foreach ($medias as $media) {
                if ($media->getId() == $child->vars['value']) {
                    $child->vars['media'] = $media->getMedia();
                    break;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'    => 'ekyna_media.media.label.plural',
                'mapped'   => false,
                'medias'     => [],
                'choices'  => function (Options $options, $value) {
                    if (empty($value)) {
                        $medias = $options['medias'];
                        /** @var ProductMediaInterface $media */
                        foreach ($medias as $media) {
                            $value['Media #' . $media->getId()] = $media->getId();
                        }
                    }

                    return $value;
                },
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.convert.help.media_choice',
                ],
            ])
            ->setAllowedTypes('medias', 'array')
            ->setAllowedValues('medias', function ($value) {
                if (empty($value)) {
                    return false;
                }

                foreach ($value as $media) {
                    if (!$media instanceof ProductMediaInterface) {
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

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_convert_media_choice';
    }
}
