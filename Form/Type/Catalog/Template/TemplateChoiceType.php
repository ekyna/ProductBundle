<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TemplateChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TemplateChoiceType extends AbstractType
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
            'label'      => 'ekyna_product.catalog.field.template',
            'choices'    => function (Options $options) {
                $choices = [];

                foreach ($this->registry->allTemplates() as $name => $config) {
                    if ($options['with_slots'] && 0 >= $config['slots']) {
                        continue;
                    }

                    $choices[$config['label']] = $name;
                }

                return $choices;
            },
            'with_slots' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
