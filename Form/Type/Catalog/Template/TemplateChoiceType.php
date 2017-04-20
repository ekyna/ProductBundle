<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class TemplateChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TemplateChoiceType extends AbstractType
{
    protected CatalogRegistry $registry;

    public function __construct(CatalogRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'                     => t('catalog.field.template', [], 'EkynaProduct'),
            'choices'                   => function (Options $options) {
                $choices = [];

                foreach ($this->registry->allTemplates() as $name => $config) {
                    if ($options['with_slots'] && 0 >= $config['slots']) {
                        continue;
                    }

                    $choices[$config['label']] = $name;
                }

                return $choices;
            },
            'choice_translation_domain' => 'EkynaProduct',
            'with_slots'                => false,
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
