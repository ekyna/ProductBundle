<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Resource as RC;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\ProductBundle\Form\Type\NewSupplierProductType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Search\ProductRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductController extends ResourceController
{
    use RC\TinymceTrait,
        RC\ToggleableTrait;


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
                ->findBySubjectAndSupplier($product, $supplier);

            if (null === $supplierProduct) {
                return $this->redirectToRoute('ekyna_commerce_supplier_product_admin_new', [
                    'supplierId' => $supplier->getId(),
                    'productId'  => $product->getId(),
                ]);
            }

            $this->addFlash($this->getTranslator()->trans('ekyna_product.product.alert.supplier_product_exists', [
                '%name%' => $supplier->getName(),
            ]), 'warning');
        }

        return $this->redirect($this->generateResourcePath($product));
    }

    /**
     * {@inheritdoc}
     */
    public function searchAction(Request $request)
    {
        //$callback = $request->query->get('callback');
        $limit = intval($request->query->get('limit'));
        $query = trim($request->query->get('query'));
        $types = $request->query->get('types');

        $repository = $this->get('fos_elastica.manager')->getRepository($this->config->getResourceClass());
        if (!$repository instanceOf ProductRepository) {
            throw new \RuntimeException('Expected instance of ' . ProductRepository::class);
        }

        if (empty($types)) {
            $results = $repository->defaultSearch($query, $limit);
        } else {
            $results = $repository->searchByTypes($query, $types, $limit);
        }

        $data = $this->container->get('serializer')->serialize([
            'results'     => $results,
            'total_count' => count($results),
        ], 'json', ['groups' => ['Default']]);

        $response = new Response($data);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
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
                /** @noinspection PhpUndefinedMethodInspection */
                if ($form->get('actions')->has('saveAndList') && $form->get('actions')->get('saveAndList')->isClicked()) {
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

        $converter = $this->get('ekyna_product.product.product_converter');

        $type = $request->attributes->get('type');

        if (!$converter->can($resource, $type)) {
            throw $this->createNotFoundException('Not yet implemented.');
        }

        $result = $converter->convert($resource, $type);
        if ($result instanceof FormInterface) {
            $formTemplate = sprintf('EkynaProductBundle:Admin/Product/Convert:_%s_form.html.twig', $type);

            return $this->render(
                $this->config->getTemplate('convert.html'),
                $context->getTemplateVars([
                    'form'          => $result->createView(),
                    'form_template' => $formTemplate,
                ])
            );
        } elseif ($result instanceof ProductInterface) {
            $event = $this->getOperator()->create($result);
            $event->toFlashes($this->getFlashBag());

            if ($event->hasErrors()) {
                return $this->redirect($this->generateResourcePath($resource));
            }

            $this->addFlash('ekyna_product.convert.variable_success', 'warning');

            return $this->redirect($this->generateResourcePath($result));
        }

        throw new \LogicException("Unexpected result.");
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
        }

        return null;
    }

    /**
     * Creates the "new supplier product" form.
     *
     * @param ProductInterface $product
     *
     * @return \Symfony\Component\Form\Form
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
}
