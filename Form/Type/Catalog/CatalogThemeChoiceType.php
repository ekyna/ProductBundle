<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog;

use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CatalogThemeChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogThemeChoiceType extends AbstractType
{
    protected CatalogRegistry $registry;

    public function __construct(CatalogRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'                     => t('field.theme', [], 'EkynaUi'),
            'choices'                   => $this->buildThemeChoices(),
            'choice_translation_domain' => 'EkynaProduct',
        ]);
    }

    /**
     * Builds the theme choices.
     */
    private function buildThemeChoices(): array
    {
        $choices = [];

        foreach ($this->registry->allThemes() as $name => $config) {
            $choices[$config['label']] = $name;
        }

        return $choices;
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
