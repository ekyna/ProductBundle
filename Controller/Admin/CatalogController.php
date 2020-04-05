<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Form\ChoiceList\SaleItemChoiceLoader;
use Ekyna\Bundle\ProductBundle\Entity\Catalog;
use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\CatalogRenderType;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\PdfException;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CatalogPageController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogController extends ResourceController
{
    /**
     * Slots form action.
     *
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
                'compound' => true,
            ])
            ->add('slots', $config['form_type']);

        $response = $this->render('@EkynaProduct/Admin/Catalog/page_slots_form.xml.twig', [
            'form' => $form->createView(),
            'name' => $request->query->get('name'),
        ]);

        $response->headers->add(['Content-Type' => 'application/xml']);
        $response->setPrivate();

        return $response;
    }

    /**
     * Catalog render action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var Catalog $catalog */
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

        $form = $this->createRenderForm($catalog, $context);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this
                    ->get('ekyna_product.catalog.renderer')
                    ->respond($catalog, $request);
            } catch (PdfException $e) {
                $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

                return $this->redirect($this->generateResourcePath($catalog));
            }
        }

        return $this->render(
            $this->config->getTemplate('render.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Render from sale action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFromSaleAction(Request $request)
    {
        $context = $this->loadContext($request);

        $this->isGranted('VIEW');

        $type = $request->attributes->get('type');
        $id = $request->attributes->get('id');

        /** @var SaleInterface $sale */
        $sale = $this->get('ekyna_commerce.' . $type . '.repository')->find($id);

        if (!$sale) {
            throw $this->createNotFoundException('Sale not found');
        }

        $loader = new SaleItemChoiceLoader($sale);

        /** @var Catalog $catalog */
        $catalog = $this->getRepository()->createNew();
        $catalog
            ->setContext(
                $this
                    ->get('ekyna_commerce.common.context_provider')
                    ->getContext($sale)
            )
            ->setDisplayPrices(false)
            ->setFormat(CatalogRenderer::FORMAT_PDF)
            ->setSaleItems($loader->loadItems());

        $form = $this->createRenderForm($catalog, $context, $sale);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $response = $this
                    ->get('ekyna_product.catalog.renderer')
                    ->respond($catalog, $request);
            } catch (PdfException $e) {
                $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

                return $this->redirect($this->generateResourcePath($sale));
            }

            if ($catalog->isSave()) {
                $this->saveSaleCatalog($sale, $response->getContent());

                return $this->redirect($this->generateResourcePath($sale));
            }

            return $response;
        }

        return $this->render('@EkynaProduct/Admin/Catalog/render_from_sale.html.twig', [
            'form' => $form->createView(),
            'sale' => $sale,
        ]);
    }

    /**
     * Saves the catalog as a sale attachment.
     *
     * @param SaleInterface $sale
     * @param string        $content
     *
     * @return bool
     */
    private function saveSaleCatalog(SaleInterface $sale, string $content)
    {
        $path = sys_get_temp_dir() . '/' . uniqid() . '.pdf';
        if (!file_put_contents($path, $content)) {
            throw new \RuntimeException("Failed to write content into file '$path'.");
        }

        // Fake uploaded file
        $file = new UploadedFile($path, 'catalog.pdf', null, null, null, true);

        // Attachment
        $attachment = $this->get('ekyna_commerce.sale_factory')->createAttachmentForSale($sale);

        $attachment
            ->setTitle('Catalog')
            ->setFile($file);

        $sale->addAttachment($attachment);

        $config = $this
            ->get('ekyna_resource.configuration_registry')
            ->findConfiguration($attachment);

        /** @var \Ekyna\Component\Resource\Operator\ResourceOperatorInterface $operator */
        $operator = $this->get($config->getServiceKey('operator'));

        $event = $operator->persist($attachment);

        foreach ($event->getErrors() as $message) {
            $this->addFlash($message->getMessage(), $message->getType());
        }

        @unlink($path);

        return !$event->hasErrors();
    }

    /**
     * Creates the render form.
     *
     * @param Catalog            $catalog
     * @param Context            $context
     * @param SaleInterface|null $sale
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createRenderForm(Catalog $catalog, Context $context, SaleInterface $sale = null)
    {
        if ($sale) {
            $action = $this->generateUrl('ekyna_product_catalog_admin_render_from_sale', [
                'type' => $context->getRequest()->attributes->get('type'),
                'id'   => $sale->getId(),
            ]);
            $cancel = $this->generateResourcePath($sale, 'show');
        } else {
            $action = $this->generateResourcePath($catalog, 'render');
            $cancel = $this->generateResourcePath($catalog, 'show');
        }

        $form = $this->createForm(CatalogRenderType::class, $catalog, [
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
            '_redirect_enabled' => true,
            'sale'              => $sale,
            'validation_groups' => ['CatalogFromSale']
        ]);

        $this->createFormFooter($form, $context, [
            'save'   => [
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
            ],
        ]);

        return $form;
    }
}
