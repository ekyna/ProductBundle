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
    public function setThemes(array $themes)
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
    public function registerTheme($name, $config)
    {
        if (isset($this->themes[$name])) {
            throw new InvalidArgumentException("Catalog theme '$name' is already registered.");
        }

        $this->themes[$name] = $config;

        return $this;
    }

    /**
     * Returns the theme path by its name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getTheme($name)
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
    public function allThemes()
    {
        return $this->themes;
    }

    /**
     * Sets the templates.
     *
     * @param array $templates
     *
     * @return CatalogRegistry
     */
    public function setTemplates(array $templates)
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
    public function registerTemplate($name, array $config)
    {
        if (isset($this->templates[$name])) {
            throw new InvalidArgumentException("Catalog template '$name' is already registered.");
        }

        $this->templates[$name] = $config;

        return $this;
    }

    /**
     * Returns the template for the given name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getTemplate($name)
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
    public function allTemplates()
    {
        return $this->templates;
    }

    /**
     * Returns the default themes.
     *
     * @return array
     */
    public static function getDefaultThemes()
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
    public static function getDefaultTemplates()
    {
        return [
            'default.cover'     => [
                'label'     => 'ekyna_product.catalog.template.cover',
                'form_type' => Type\CoverType::class,
                'directory' => '@EkynaProduct/Catalog/Template/Cover',
                'slots'     => 0,
                'mockup'    => 'null',
            ],
            'default.full'      => [
                'label'     => 'ekyna_product.catalog.template.full',
                'form_type' => Type\FullType::class,
                'directory' => '@EkynaProduct/Catalog/Template/Full',
                'slots'     => 1,
                'mockup'    => 'null',
            ],
            'default.half'      => [
                'label'     => 'ekyna_product.catalog.template.half',
                'form_type' => Type\HalfType::class,
                'directory' => '@EkynaProduct/Catalog/Template/Half',
                'slots'     => 2,
                'mockup'    => 'null',
            ],
            'default.half_dual' => [
                'label'     => 'ekyna_product.catalog.template.half_dual',
                'form_type' => Type\HalfDualType::class,
                'directory' => '@EkynaProduct/Catalog/Template/HalfDual',
                'slots'     => 3,
                'mockup'    => 'null',
            ],
            'default.quarter' => [
                'label'     => 'ekyna_product.catalog.template.quarter',
                'form_type' => Type\QuarterType::class,
                'directory' => '@EkynaProduct/Catalog/Template/Quarter',
                'slots'     => 4,
                'mockup'    => 'null',
            ],
        ];
    }
}
