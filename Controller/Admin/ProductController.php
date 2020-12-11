<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\Resource as RC;
use Ekyna\Bundle\CommerceBundle\Controller\Admin\AbstractSubjectController;
use Ekyna\Bundle\ProductBundle\Exception\ProductExceptionInterface;
use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Form\Type\ExportConfigType;
use Ekyna\Bundle\ProductBundle\Form\Type\NewSupplierProductType;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Converter\ProductConverter;
use Ekyna\Bundle\ProductBundle\Service\Exporter\ProductExporter;
use Ekyna\Bundle\ProductBundle\Service\Features;
use Ekyna\Bundle\ProductBundle\Service\Generator\ExternalReferenceGenerator;
use Ekyna\Component\Resource\Search\Request as SearchRequest;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Intl\Intl;

/**
 * Class ProductController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductController extends AbstractSubjectController
{
    use RC\TinymceTrait,
        RC\ToggleableTrait;

    /**
     * Product summary action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function summaryAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);
        /** @var ProductInterface $product */
        $product = $context->getResource();

        $this->isGranted('VIEW', $product);

        $response = new Response();
        $response->setVary(['Accept', 'Accept-Encoding']);
        $response->setExpires(new \DateTime('+3 min'));
        //$response->setLastModified($product->getUpdatedAt());

        $html = false;
        $accept = $request->getAcceptableContentTypes();

        if (in_array('application/json', $accept, true)) {
            $response->headers->add(['Content-Type' => 'application/json']);
        } elseif (in_array('text/html', $accept, true)) {
            $html = true;
        } else {
            throw $this->createNotFoundException("Unsupported content type.");
        }

        /*if ($response->isNotModified($request)) {
            return $response;
        }*/

        if ($html) {
            $content = $this->get('serializer')->normalize($product, 'json', ['groups' => ['Summary']]);
            $content = $this->renderView('@EkynaProduct/Admin/Product/summary.html.twig', $content);
        } else {
            $content = $this->get('serializer')->serialize($product, 'json', ['groups' => ['Summary']]);
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * Create supplier product action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newSupplierProductAction(Request $request)
    {
        if ($isXhr = $request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not yet implemented.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var ProductInterface $product */
        $product = $context->getResource($resourceName);

        $form = $this->createNewSupplierProductForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierInterface $supplier */
            $supplier = $form->get('supplier')->getData();

            $supplierProduct = $this
                ->get('ekyna_commerce.supplier_product.repository')
                ->findOneBySubjectAndSupplier($product, $supplier);

            if (null === $supplierProduct) {
                return $this->redirectToRoute('ekyna_commerce_supplier_product_admin_new', [
                    'supplierId' => $supplier->getId(),
                    'provider'   => $product::getProviderName(),
                    'identifier' => $product->getIdentifier(),
                ]);
            }

            $this->addFlash($this->getTranslator()->trans('ekyna_product.product.alert.supplier_product_exists', [
                '%name%' => $supplier->getName(),
            ]), 'warning');
        } else {
            $errors = '';
            foreach ($form->getErrors(true) as $error) {
                $errors .= $error->getMessage() . '<br>';
            }
            $this->addFlash($errors, 'danger');
        }

        return $this->redirect($this->generateResourcePath($product));
    }

    /**
     * Product duplicate action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function duplicateAction(Request $request)
    {
        $this->isGranted('CREATE');

        if ($isXhr = $request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not yet implemented.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        // Source
        /** @var ProductInterface $source */
        $source = $context->getResource($resourceName);
        $context->addResource('source', $source);

        // TODO Temporary lock
        if ($source->getType() !== ProductTypes::TYPE_SIMPLE) {
            throw $this->createNotFoundException('Not yet implemented.');
        }

        $target = clone $source;
        $context->addResource($resourceName, $target);

        $form = $this->createNewResourceForm($context, !$isXhr, [
            'action' => $this->generateResourcePath($source, 'duplicate'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($target->getTranslations() as $translation) {
                $translation->setSlug(null);
            }

            // TODO use ResourceManager
            $event = $this->getOperator()->create($target);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    // TODO use resource serializer
                    return JsonResponse::create([
                        'id'   => $target->getId(),
                        'name' => (string)$target,
                    ]);
                }

                $redirectPath = null;
                if ($form->get('actions')->has('saveAndList')
                    && $form->get('actions')->get('saveAndList')->isClicked()) {
                    $redirectPath = $this->generateResourcePath($target, 'list');
                } elseif (null === $redirectPath = $form->get('_redirect')->getData()) {
                    if ($this->hasParent() && null !== $parentResource = $this->getParentResource($context)) {
                        $redirectPath = $this->generateResourcePath($parentResource, 'show');
                    } else {
                        $redirectPath = $this->generateResourcePath($target, 'show');
                    }
                }

                return $this->redirect($redirectPath);
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('duplicate');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_duplicate', $resourceName),
            'ekyna_core.button.duplicate'
        );

        return $this->render(
            $this->config->getTemplate('duplicate.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Product attributes form action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function attributesFormAction(Request $request)
    {
        $this->isGranted('CREATE');

        // Assert XHR
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        // Product
        // TODO Find the product if edit
        $context = $this->loadContext($request);
        /** @var ProductInterface $product */
        $product = parent::createNew($context);
        $product->setType(ProductTypes::TYPE_SIMPLE);

        // Attribute Set
        /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface $attributeSet */
        $attributeSet = $this->get('ekyna_product.attribute_set.repository')->find(
            $request->attributes->get('attributeSetId')
        );
        if (null === $attributeSet) {
            throw $this->createNotFoundException();
        }

        // Form
        $form = $this->get('form.factory')->createNamed('FORM__NAME', Type\FormType::class, $product, [
            'block_name' => 'product',
        ]);

        $builder = $this->get('ekyna_product.product.form_type.builder');
        $builder
            ->initialize($product, $form)
            ->addAttributesField($attributeSet);

        $response = $this->render('@EkynaProduct/Admin/Product/attributes_form.xml.twig', [
            'form' => $form->createView(),
        ]);

        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    /**
     * Product convert action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function convertAction(Request $request)
    {
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var ProductInterface $resource */
        $resource = $context->getResource($resourceName);

        $converter = $this->get(ProductConverter::class);

        $sourceType = $resource->getType();
        $targetType = $request->attributes->get('type');

        if (!$converter->can($resource, $targetType)) {
            throw $this->createNotFoundException('Not yet implemented.');
        }

        $event = $converter->convert($resource, $targetType);
        if ($form = $event->getForm()) {
            $formTemplate = sprintf(
                'EkynaProductBundle:Admin/Product/Convert:_%s_to_%s_form.html.twig',
                $sourceType, $targetType
            );

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }

            return $this->render(
                $this->config->getTemplate('convert.html'),
                $context->getTemplateVars([
                    'form'          => $form->createView(),
                    'form_template' => $formTemplate,
                ])
            );
        }

        $event->toFlashes($this->getFlashBag());

        if ($event->isSuccess()) {
            $this->addFlash('ekyna_product.convert.success', 'success');

            return $this->redirect($this->generateResourcePath($event->getTarget()));
        }

        return $this->redirect($this->generateResourcePath($resource));
    }

    /**
     * Move (variant) up.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function moveUpAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var ProductInterface $variant */
        $variant = $context->getResource($resourceName);

        $this->isGranted('EDIT', $variant);

        if ($variant->getType() !== ProductTypes::TYPE_VARIANT) {
            return $this->redirect($this->generateResourcePath($variant));
        }

        $manager = $this->getManager();

        $variable = $variant->getParent();
        $variants = $variable->getVariants();

        $swapPosition = $variant->getPosition() - 1;
        foreach ($variants as $swap) {
            if ($swap->getPosition() === $swapPosition) {
                $swap->setPosition($swap->getPosition() + 1);
                $manager->persist($swap);

                $variant->setPosition($variant->getPosition() - 1);
                $manager->persist($variant);

                $manager->flush();
                break;
            }
        }

        return $this->redirect($this->generateResourcePath($variable));
    }

    /**
     * Move (variant) down.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function moveDownAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var ProductInterface $variant */
        $variant = $context->getResource($resourceName);

        $this->isGranted('EDIT', $variant);

        if ($variant->getType() !== ProductTypes::TYPE_VARIANT) {
            return $this->redirect($this->generateResourcePath($variant));
        }

        $manager = $this->getManager();

        $variable = $variant->getParent();
        $variants = $variable->getVariants();

        $swapPosition = $variant->getPosition() + 1;
        foreach ($variants as $swap) {
            if ($swap->getPosition() === $swapPosition) {
                $swap->setPosition($swap->getPosition() - 1);
                $manager->persist($swap);

                $variant->setPosition($variant->getPosition() + 1);
                $manager->persist($variant);

                $manager->flush();
                break;
            }
        }

        return $this->redirect($this->generateResourcePath($variable));
    }

    /**
     * Generates the product external reference.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function generateReference(Request $request): Response
    {
        $context = $this->loadContext($request);
        /** @var ProductInterface $product */
        $product = $context->getResource();

        $this->isGranted('EDIT', $product);

        $type = $request->attributes->get('type');

        switch ($type) {
            case ProductReferenceTypes::TYPE_EAN_13:
                try {
                    $this->get(ExternalReferenceGenerator::class)->generateGtin13($product);
                } catch (ProductExceptionInterface $exception) {
                    $this->addFlash($exception->getMessage(), 'danger');
                }

                $event = $this->getOperator()->update($product);
                $event->toFlashes($this->getFlashBag());

                break;

            default:
                throw new RuntimeException("Unsupported reference type.");
        }

        return $this->redirect($this->generateResourcePath($product, 'show'));
    }

    /**
     * Invalidates the product offers and prices.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invalidateOffers(Request $request): Response
    {
        $context = $this->loadContext($request);
        /** @var ProductInterface $product */
        $product = $context->getResource();

        $this->isGranted('EDIT', $product);

        $product
            ->setPendingOffers(true)
            ->setPendingPrices(true);

        $event = $this->getOperator()->update($product);
        $event->toFlashes($this->getFlashBag());

        return $this->redirect($this->generateResourcePath($product, 'show'));
    }

    /**
     * Products export action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request): Response
    {
        $this->isGranted('VIEW');

        $context = $this->loadContext($request);

        $config = new ExportConfig(
            $this->get('ekyna_commerce.common.context_provider')->getContext()
        );

        $form = $this->createExportForm($config, $request);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $path = null;
            try {
                $path = $this->get(ProductExporter::class)->export($config);
            } catch (ProductExceptionInterface $e) {
                $this->addFlash($e->getMessage(), 'danger');
            }

            if ($path) {
                clearstatcache(true, $path);

                $stream = new Stream($path);
                $response = new BinaryFileResponse($stream);

                // TODO Regarding to config format
                $response->headers->set('Content-Type', 'text/csv');
                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'products.csv'
                );
                $response->headers->set('Content-Disposition', $disposition);

                return $response;
            }
        }

        $this->appendBreadcrumb(
            sprintf('%s_export', $context->getConfiguration()->getResourceName()),
            'ekyna_product.product.button.export'
        );

        return $this->render(
            $this->config->getTemplate('export.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * @inheritdoc
     */
    protected function createNew(Context $context)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $resource */
        $resource = parent::createNew($context);

        $request = $context->getRequest();

        $type = $request->attributes->get('type');
        if (!ProductTypes::isValid($type)) {
            throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
        }

        $resource->setType($type);

        if ($type === ProductTypes::TYPE_VARIANT) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $parent */
            $parent = $this->getRepository()->find($request->query->get('parent'));
            if (null === $parent || $parent->getType() !== ProductTypes::TYPE_VARIABLE) {
                throw new \InvalidArgumentException('Invalid parent.');
            }
            $resource->setParent($parent);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function buildShowData(array &$data, Context $context)
    {
        /** @var ProductInterface $product */
        $product = $context->getResource();

        if ($product->isPendingOffers() || $product->isPendingPrices()) {
            $this->addFlash('ekyna_product.product.alert.pending_offers', 'warning');
        }

        if ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            $table = $this
                ->getTableFactory()
                ->createTable('variants', $this->config->getTableType(), [
                    'variant_mode' => true,
                    'source'       => $product->getVariants()->toArray(),
                ]);

            if (null !== $response = $table->handleRequest($context->getRequest())) {
                return $response;
            }

            $data['variants'] = $table->createView();
        } elseif (ProductTypes::isChildType($product->getType())) {
            $type = $this->get('ekyna_commerce.supplier_product.configuration')->getTableType();

            $table = $this
                ->getTableFactory()
                ->createTable('supplierProducts', $type, [
                    'subject' => $product,
                ]);

            if (null !== $response = $table->handleRequest($context->getRequest())) {
                return $response;
            }

            $data['supplierProducts'] = $table->createView();

            $data['newSupplierProductForm'] = $this
                ->createNewSupplierProductForm($product)
                ->createView();

            if (null === $product->getReferenceByType(ProductReferenceTypes::TYPE_EAN_13)) {
                if ($this->get(Features::class)->isEnabled(Features::GTIN13_GENERATOR)) {
                    $this->addFlash($this->getTranslator()->trans('ekyna_product.product.alert.generate_gtin_13', [
                        '%url%' => $this->generateResourcePath($product, 'generate_reference', [
                            'type' => ProductReferenceTypes::TYPE_EAN_13,
                        ]),
                    ]), 'warning');
                }
            }
        }

        /** @var \Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface $repository */
        $repository = $this->getRepository();

        $data['bundleParents'] = $repository->findParentsByBundled($product);
        $data['optionParents'] = $repository->findParentsByOptionProduct($product);
        $data['componentParents'] = $repository->findParentsByComponent($product);
        $data['offers_list'] = $this->getOffersList($product);
        $data['prices_list'] = $this->getPricesList($product);

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function createSearchRequest(Request $request): SearchRequest
    {
        $searchRequest = parent::createSearchRequest($request);

        $searchRequest->setParameter('types', (array)$request->query->get('types'));

        return $searchRequest;
    }

    /**
     * Creates the "new supplier product" form.
     *
     * @param ProductInterface $product
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createNewSupplierProductForm(ProductInterface $product)
    {
        return $this->createForm(NewSupplierProductType::class, null, [
            'action' => $this->generateUrl('ekyna_product_product_admin_new_supplier_product', [
                'productId' => $product->getId(),
            ]),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function generateResourcePath($resource, $action = 'show', array $parameters = [])
    {
        if ($resource instanceof ProductInterface && $action === 'new') {
            if (!array_key_exists('type', $parameters)) {
                $parameters['type'] = $resource->getType();
            }
        }

        return parent::generateResourcePath($resource, $action, $parameters);
    }

    /**
     * Returns the product's offer list.
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getOffersList(ProductInterface $product)
    {
        $offers = $this
            ->get('ekyna_product.offer.repository')
            ->findByProduct($product);

        $translator = $this->get('translator');
        $allGroups = $translator->trans('ekyna_commerce.customer_group.message.all');
        $allCountries = $translator->trans('ekyna_commerce.country.message.all');

        $list = [];
        foreach ($offers as $offer) {
            $group = $offer->getGroup();
            $country = $offer->getCountry();

            $key = sprintf(
                "%d-%d",
                $group ? $group->getId() : 0,
                $country ? $country->getId() : 0
            );

            $locale = $this->get('ekyna_resource.locale_provider')->getCurrentLocale();
            $region = Intl::getRegionBundle();

            if (!isset($list[$key])) {
                $list[$key] = [
                    'title'  => sprintf(
                        "%s / %s",
                        $group ? $group->getName() : $allGroups,
                        $country ? $region->getCountryName($country->getCode(), $locale) : $allCountries
                    ),
                    'offers' => [],
                ];
            }

            $list[$key]['offers'][] = $offer;
        }

        $list = array_reverse($list);

        foreach ($list as &$data) {
            $data['offers'] = array_reverse($data['offers']);
        }

        return $list;
    }

    /**
     * Returns the product's prices list.
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getPricesList(ProductInterface $product)
    {
        $prices = $this
            ->get('ekyna_product.price.repository')
            ->findByProduct($product);

        $translator = $this->get('translator');
        $allGroups = $translator->trans('ekyna_commerce.customer_group.message.all');
        $allCountries = $translator->trans('ekyna_commerce.country.message.all');

        $list = [];
        foreach ($prices as $price) {
            $group = $price->getGroup();
            $country = $price->getCountry();

            $key = sprintf(
                "%d-%d",
                $group ? $group->getId() : 0,
                $country ? $country->getId() : 0
            );

            $locale = $this->get('ekyna_resource.locale_provider')->getCurrentLocale();
            $region = Intl::getRegionBundle();

            if (!isset($list[$key])) {
                $list[$key] = [
                    'title'  => sprintf(
                        "%s / %s",
                        $group ? $group->getName() : $allGroups,
                        $country ? $region->getCountryName($country->getCode(), $locale) : $allCountries
                    ),
                    'prices' => [],
                ];
            }

            $list[$key]['prices'][] = $price;
        }

        $list = array_reverse($list);

        foreach ($list as &$data) {
            $data['prices'] = array_reverse($data['prices']);
        }

        return $list;
    }

    /**
     * Creates the export form.
     *
     * @param ExportConfig $data
     * @param Request      $request
     *
     * @return FormInterface
     */
    private function createExportForm(ExportConfig $data, Request $request): FormInterface
    {
        $action = $this->generateResourcePath($this->getConfiguration()->getResourceId(), 'export');

        $form = $this->createForm(ExportConfigType::class, $data, [
            'action'            => $action,
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal'],
            '_redirect_enabled' => true,
        ]);

        $referer = $request->headers->get('referer');
        if (0 < strlen($referer) && false === strpos($referer, $action)) {
            $cancel = $referer;
        } else {
            $cancel = $this->generateResourcePath($this->getConfiguration()->getResourceId(), 'list');
        }

        $form->add('actions', FormActionsType::class, [
            'buttons' => [
                'remove' => [
                    'type'    => Type\SubmitType::class,
                    'options' => [
                        'button_class' => 'success',
                        'label'        => 'ekyna_core.button.export',
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
            ],
        ]);

        return $form;
    }
}
