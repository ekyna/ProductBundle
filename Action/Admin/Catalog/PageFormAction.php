<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Catalog;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template\SlotsType;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Twig\Environment;

/**
 * Class PageFormAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Catalog
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PageFormAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    private CatalogRegistry $catalogRegistry;
    private FormFactoryInterface $formFactory;
    private Environment $twig;

    public function __construct(CatalogRegistry $catalogRegistry, FormFactoryInterface $formFactory, Environment $twig)
    {
        $this->catalogRegistry = $catalogRegistry;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
    }

    public function __invoke(): Response
    {
        $config = $this
            ->catalogRegistry
            ->getTemplate($this->request->attributes->get('template'));

        $page = new CatalogPage(); // TODO edit => fetch

        $form = $this
            ->formFactory
            ->createNamed('page__name', FormType::class, $page, [
                'compound' => true,
            ]);

        if (0 < $count = $config['slots']) {
            $form->add('slots', SlotsType::class, [
                'slot_count' => $count,
            ]);
        }

        if (!empty($type = $config['form_type'])) {
            $form->add('options', $type);
        }

        $content = $this->twig->render($this->options['template'], [
            'form' => $form->createView(),
            'name' => $this->request->query->get('name'),
        ]);

        $response = new Response($content);
        $response->headers->add(['Content-Type' => 'application/xml']);
        $response->setPrivate();

        return $response;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_catalog_page_form',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_page_form',
                'path'     => '/page-form/{template}',
                'methods'  => ['GET', 'POST'],
            ],
            'options'    => [
                'template' => '@EkynaProduct/Admin/Catalog/page_form.xml.twig',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'template' => '[a-z0-9\\._]+',
        ]);
    }
}
