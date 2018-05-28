<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\CatalogRenderType;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CatalogPageController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogController extends ResourceController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function slotsFormAction(Request $request)
    {
        $config = $this
            ->get('ekyna_product.catalog.registry')
            ->getTemplate($request->attributes->get('template'));

        $page = new CatalogPage(); // TODO edit => fetch

        $form = $this
            ->get('form.factory')
            ->createNamed('page__name', Type\FormType::class, $page, [
                'compound'   => true,
            ])
            ->add('slots', $config['form_type']);

        $response = $this->render('EkynaProductBundle:Admin/Catalog:page_slots_form.xml.twig', [
            'form' => $form->createView(),
            'name' => $request->query->get('name')
        ]);

        $response->headers->add(['Content-Type' => 'application/xml']);
        $response->setPrivate();

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function renderAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Bundle\ProductBundle\Entity\Catalog $catalog */
        $catalog = $context->getResource($resourceName);

        $this->isGranted('VIEW', $catalog);

        $catalog
            ->setContext(
                $this
                    ->get('ekyna_commerce.common.context_provider')
                    ->getContext()
            )
            ->setDisplayPrices(true)
            ->setFormat(CatalogRenderer::FORMAT_PDF);

        $action = $this->generateResourcePath($catalog, 'render');
        $cancel = $this->generateResourcePath($catalog, 'show');

        $form = $this->createForm(CatalogRenderType::class, $catalog, [
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
            'admin_mode'        => true,
            '_redirect_enabled' => true,
        ]);

        $this->createFormFooter($form, $context, [
            'save' => [
                'type'    => Type\SubmitType::class,
                'options' => [
                    'button_class' => 'primary',
                    'label'        => 'ekyna_core.button.display',
                    'attr'         => ['icon' => 'ok'],
                ],
            ],
            'cancel' => [
                'type'    => Type\ButtonType::class,
                'options' => [
                    'label'        => 'ekyna_core.button.cancel',
                    'button_class' => 'default',
                    'as_link'      => true,
                    'attr'         => [
                        'class' => 'form-cancel-btn',
                        'icon'  => 'remove',
                        'href'  => $cancel,
                    ],
                ],
            ]
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this
                ->get('ekyna_product.catalog.renderer')
                ->respond($catalog, $request);
        }

        return $this->render(
            $this->config->getTemplate('render.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }
}