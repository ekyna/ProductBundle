<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog;

use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CatalogThemeChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogThemeChoiceType extends AbstractType
{
    /**
     * @var CatalogRegistry
     */
    protected $registry;


    /**
     * Constructor.
     *
     * @param CatalogRegistry $registry
     */
    public function __construct(CatalogRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'   => 'ekyna_core.field.theme',
            'choices' => $this->buildThemeChoices(),
        ]);
    }

    /**
     * Builds the theme choices.
     *
     * @return array
     */
    private function buildThemeChoices()
    {
        $choices = [];

        foreach ($this->registry->allThemes() as $name => $config) {
            $choices[$config['label']] = $name;
        }

        return $choices;
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
