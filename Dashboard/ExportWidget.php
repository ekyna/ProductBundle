<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class AccountingWidget
 * @package Ekyna\Bundle\ProductBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportWidget extends AbstractWidgetType
{
    public const NAME = 'product_export';

    public static function getName(): string
    {
        return self::NAME;
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        return $twig->render('@EkynaProduct/Admin/Dashboard/widget_export.html.twig');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'frame'    => false,
            'position' => 9996,
            'col_md'   => 6,
            'css_path' => 'bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }
}
