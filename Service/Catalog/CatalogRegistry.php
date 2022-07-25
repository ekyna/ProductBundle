<?php

declare(strict_types=1);

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
    private array $themes = [];
    private array $templates = [];

    public function __construct(array $themes, array $templates)
    {
        $this->setThemes($themes);
        $this->setTemplates($templates);
    }

    public function setThemes(array $themes): CatalogRegistry
    {
        foreach ($themes as $name => $config) {
            $this->registerTheme($name, $config);
        }

        return $this;
    }

    public function registerTheme(string $name, array $config): CatalogRegistry
    {
        if (isset($this->themes[$name])) {
            throw new InvalidArgumentException("Catalog theme '$name' is already registered.");
        }

        $this->themes[$name] = $config;

        return $this;
    }

    public function setTemplates(array $templates): CatalogRegistry
    {
        foreach ($templates as $name => $config) {
            $this->registerTemplate($name, $config);
        }

        return $this;
    }

    public function registerTemplate(string $name, array $config): CatalogRegistry
    {
        if (isset($this->templates[$name])) {
            throw new InvalidArgumentException("Catalog template '$name' is already registered.");
        }

        $this->templates[$name] = $config;

        return $this;
    }

    public static function getDefaultThemes(): array
    {
        return [
            'default' => [
                'label' => 'catalog.theme.default',
                'path'  => '@EkynaProduct/Catalog/Theme/default.html.twig',
                'css'   => 'bundles/ekynaproduct/css/catalog/default-theme.css',
            ],
        ];
    }

    public static function getDefaultTemplates(): array
    {
        return [
            'default.cover'     => [
                'label'     => 'catalog.template.cover',
                'form_type' => Type\CoverType::class,
                'directory' => '@EkynaProduct/Catalog/Template/Cover',
                'slots'     => 0,
                'mockup'    => null,
            ],
            'default.separator' => [
                'label'     => 'catalog.template.separator',
                'form_type' => Type\SeparatorType::class,
                'directory' => '@EkynaProduct/Catalog/Template/Separator',
                'slots'     => 0,
                'mockup'    => null,
            ],
            'default.full'      => [
                'label'     => 'catalog.template.full',
                'directory' => '@EkynaProduct/Catalog/Template/Full',
                'form_type' => null,
                'slots'     => 1,
                'mockup'    => null,
            ],
            'default.half'      => [
                'label'     => 'catalog.template.half',
                'directory' => '@EkynaProduct/Catalog/Template/Half',
                'form_type' => null,
                'slots'     => 2,
                'mockup'    => null,
            ],
            'default.half_dual' => [
                'label'     => 'catalog.template.half_dual',
                'directory' => '@EkynaProduct/Catalog/Template/HalfDual',
                'form_type' => null,
                'slots'     => 3,
                'mockup'    => null,
            ],
            'default.quarter'   => [
                'label'     => 'catalog.template.quarter',
                'directory' => '@EkynaProduct/Catalog/Template/Quarter',
                'form_type' => null,
                'slots'     => 4,
                'mockup'    => null,
            ],
            'default.sixth'   => [
                'label'     => 'catalog.template.sixth',
                'directory' => '@EkynaProduct/Catalog/Template/Sixth',
                'form_type' => null,
                'slots'     => 6,
                'mockup'    => null,
            ],
            'default.eighth'   => [
                'label'     => 'catalog.template.eighth',
                'directory' => '@EkynaProduct/Catalog/Template/Eighth',
                'form_type' => null,
                'slots'     => 8,
                'mockup'    => null,
            ],
        ];
    }

    public function getTheme(string $name): array
    {
        if (!isset($this->themes[$name])) {
            throw new InvalidArgumentException("Catalog theme '$name' is not registered.");
        }

        return $this->themes[$name];
    }

    public function allThemes(): array
    {
        return $this->themes;
    }

    public function getTemplate(string $name): array
    {
        if (!$this->templates[$name]) {
            throw new InvalidArgumentException("Catalog template '$name' is not registered.");
        }

        return $this->templates[$name];
    }

    public function allTemplates(): array
    {
        return $this->templates;
    }
}
