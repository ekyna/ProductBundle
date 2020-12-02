<?php

namespace Ekyna\Bundle\ProductBundle\Service\Catalog;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template as Type;

/**
 * Class CatalogRegistry
 * @package Ekyna\Bundle\ProductBundle\Service\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogRegistry
{
    /**
     * @var array
     */
    private $themes = [];

    /**
     * @var array
     */
    private $templates = [];


    /**
     * Constructor.
     *
     * @param array $themes
     * @param array $templates
     */
    public function __construct(array $themes, array $templates)
    {
        $this->setThemes($themes);
        $this->setTemplates($templates);
    }

    /**
     * Sets the themes.
     *
     * @param array $themes
     *
     * @return CatalogRegistry
     */
    public function setThemes(array $themes): CatalogRegistry
    {
        foreach ($themes as $name => $config) {
            $this->registerTheme($name, $config);
        }

        return $this;
    }

    /**
     * Registers the given theme.
     *
     * @param string $name
     * @param array  $config
     *
     * @return CatalogRegistry
     */
    public function registerTheme(string $name, array $config): CatalogRegistry
    {
        if (isset($this->themes[$name])) {
            throw new InvalidArgumentException("Catalog theme '$name' is already registered.");
        }

        $this->themes[$name] = $config;

        return $this;
    }

    /**
     * Sets the templates.
     *
     * @param array $templates
     *
     * @return CatalogRegistry
     */
    public function setTemplates(array $templates): CatalogRegistry
    {
        foreach ($templates as $name => $config) {
            $this->registerTemplate($name, $config);
        }

        return $this;
    }

    /**
     * Registers the given template.
     *
     * @param string $name
     * @param array  $config
     *
     * @return CatalogRegistry
     */
    public function registerTemplate(string $name, array $config): CatalogRegistry
    {
        if (isset($this->templates[$name])) {
            throw new InvalidArgumentException("Catalog template '$name' is already registered.");
        }

        $this->templates[$name] = $config;

        return $this;
    }

    /**
     * Returns the default themes.
     *
     * @return array
     */
    public static function getDefaultThemes(): array
    {
        return [
            'default' => [
                'label' => 'ekyna_product.catalog.theme.default',
                'path'  => '@EkynaProduct/Catalog/Theme/default.html.twig',
                'css'   => 'bundles/ekynaproduct/css/catalog/default-theme.css',
            ],
        ];
    }

    /**
     * Returns the default templates.
     *
     * @return array
     */
    public static function getDefaultTemplates(): array
    {
        return [
            'default.cover'     => [
                'label'     => 'ekyna_product.catalog.template.cover',
                'form_type' => Type\CoverType::class,
                'directory' => '@EkynaProduct/Catalog/Template/Cover',
                'slots'     => 0,
                'mockup'    => null,
            ],
            'default.separator' => [
                'label'     => 'ekyna_product.catalog.template.separator',
                'form_type' => Type\SeparatorType::class,
                'directory' => '@EkynaProduct/Catalog/Template/Separator',
                'slots'     => 0,
                'mockup'    => null,
            ],
            'default.full'      => [
                'label'     => 'ekyna_product.catalog.template.full',
                'directory' => '@EkynaProduct/Catalog/Template/Full',
                'form_type' => null,
                'slots'     => 1,
                'mockup'    => null,
            ],
            'default.half'      => [
                'label'     => 'ekyna_product.catalog.template.half',
                'directory' => '@EkynaProduct/Catalog/Template/Half',
                'form_type' => null,
                'slots'     => 2,
                'mockup'    => null,
            ],
            'default.half_dual' => [
                'label'     => 'ekyna_product.catalog.template.half_dual',
                'directory' => '@EkynaProduct/Catalog/Template/HalfDual',
                'form_type' => null,
                'slots'     => 3,
                'mockup'    => null,
            ],
            'default.quarter'   => [
                'label'     => 'ekyna_product.catalog.template.quarter',
                'directory' => '@EkynaProduct/Catalog/Template/Quarter',
                'form_type' => null,
                'slots'     => 4,
                'mockup'    => null,
            ],
        ];
    }

    /**
     * Returns the theme path by its name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getTheme(string $name): array
    {
        if (!isset($this->themes[$name])) {
            throw new InvalidArgumentException("Catalog theme '$name' is not registered.");
        }

        return $this->themes[$name];
    }

    /**
     * Returns the registered themes.
     *
     * @return array
     */
    public function allThemes(): array
    {
        return $this->themes;
    }

    /**
     * Returns the template for the given name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getTemplate(string $name): array
    {
        if (!$this->templates[$name]) {
            throw new InvalidArgumentException("Catalog template '$name' is not registered.");
        }

        return $this->templates[$name];
    }

    /**
     * Returns the registered templates.
     *
     * @return array
     */
    public function allTemplates(): array
    {
        return $this->templates;
    }
}
