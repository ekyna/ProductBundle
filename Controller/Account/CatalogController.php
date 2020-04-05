<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Controller\Account\AbstractController;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\CatalogRenderType;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\CatalogType;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Ekyna\Component\Commerce\Exception\PdfException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CatalogController
 * @package Ekyna\Bundle\ProductBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogController extends AbstractController
{
    /**
     * Display the customer's catalog list.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Ekyna\Bundle\CoreBundle\Exception\RedirectException
     */
    public function indexAction()
    {
        $customer = $this->getCustomerOrRedirect();

        $catalogs = $this->getRepository()->findByCustomer($customer);

        return $this->render('@EkynaProduct/Account/Catalog/index.html.twig', [
            'customer' => $customer,
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * Creates a new catalog.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        /** @var \Ekyna\Bundle\ProductBundle\Entity\Catalog $catalog */
        $catalog = $this->getRepository()->createNew();
        $catalog
            ->setCustomer($customer)
            ->setTheme('default');

        $operator = $this->getOperator();

        $operator->initialize($catalog);

        $form = $this->createForm(CatalogType::class, $catalog, [
            'action'   => $this->generateUrl('ekyna_product_account_catalog_new'),
            'method'   => 'POST',
            'customer' => true,
        ]);

        $this->createFormFooter($form, [
            'cancel_path' => $this->generateUrl('ekyna_product_account_catalog_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $operator->create($catalog);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_product.account.catalog.new.success', 'success');

                return $this->redirectToRoute('ekyna_product_account_catalog_show', [
                    'catalogId' => $catalog->getId(),
                ]);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $catalogs = $this->getRepository()->findByCustomer($customer);

        return $this->render('@EkynaProduct/Account/Catalog/create.html.twig', [
            'customer' => $customer,
            'form'     => $form->createView(),
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * Show the catalog.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $catalog = $this->findCatalog($customer, intval($request->attributes->get('catalogId')));

        $catalogs = $this->getRepository()->findByCustomer($customer);

        return $this->render('@EkynaProduct/Account/Catalog/show.html.twig', [
            'customer' => $customer,
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * Edits the catalog.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $catalog = $this->findCatalog($customer, intval($request->attributes->get('catalogId')));

        $form = $this->createForm(CatalogType::class, $catalog, [
            'action'   => $this->generateUrl('ekyna_product_account_catalog_edit', [
                'catalogId' => $catalog->getId(),
            ]),
            'method'   => 'POST',
            'customer' => true,
        ]);

        $this->createFormFooter($form, [
            'cancel_path' => $this->generateUrl('ekyna_product_account_catalog_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->getOperator()->update($catalog);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_product.account.catalog.edit.success', 'success');

                return $this->redirectToRoute('ekyna_product_account_catalog_show', [
                    'catalogId' => $catalog->getId(),
                ]);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $catalogs = $this->getRepository()->findByCustomer($customer);

        return $this->render('@EkynaProduct/Account/Catalog/edit.html.twig', [
            'customer' => $customer,
            'catalog'  => $catalog,
            'form'     => $form->createView(),
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * Removes the catalog.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $catalog = $this->findCatalog($customer, intval($request->attributes->get('catalogId')));

        // TODO Form

        $catalogs = $this->getRepository()->findByCustomer($customer);

        return $this->render('@EkynaProduct/Account/Catalog/show.html.twig', [
            'customer' => $customer,
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * Prints the catalog.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function printAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $catalog = $this->findCatalog($customer, intval($request->attributes->get('catalogId')));

        $catalog
            ->setContext(
                $this
                    ->get('ekyna_commerce.common.context_provider')
                    ->getContext()
            )
            ->setDisplayPrices(false)
            ->setFormat(CatalogRenderer::FORMAT_PDF);

        $cancelPath = $this->generateUrl('ekyna_product_account_catalog_show', [
            'catalogId' => $catalog->getId(),
        ]);

        // TODO not all fields

        $form = $this->createForm(CatalogRenderType::class, $catalog, [
            'action' => $this->generateUrl('ekyna_product_account_catalog_print', [
                'catalogId' => $catalog->getId(),
            ]),
            'method' => 'POST',
        ]);

        $this->createFormFooter($form, [
            'cancel_path'  => $cancelPath,
            'submit_label' => 'ekyna_core.button.display',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this
                    ->get('ekyna_product.catalog.renderer')
                    ->respond($catalog, $request);
            } catch (PdfException $e) {
                $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

                return $this->redirect($cancelPath);
            }
        }

        $catalogs = $this->getRepository()->findByCustomer($customer);

        return $this->render('@EkynaProduct/Account/Catalog/print.html.twig', [
            'customer' => $customer,
            'catalog'  => $catalog,
            'form'     => $form->createView(),
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * @param CustomerInterface $customer
     * @param int               $id
     *
     * @return \Ekyna\Bundle\ProductBundle\Entity\Catalog|null
     */
    protected function findCatalog(CustomerInterface $customer, int $id)
    {
        $catalog = $this->getRepository()->findOneByCustomerAndId($customer, $id);

        if (null === $catalog) {
            throw $this->createNotFoundException('Catalog not found.');
        }

        return $catalog;
    }

    /**
     * Returns the catalog repository.
     *
     * @return \Ekyna\Bundle\ProductBundle\Repository\CatalogRepository
     */
    protected function getRepository()
    {
        return $this->get('ekyna_product.catalog.repository');
    }

    /**
     * Returns the catalog operator.
     *
     * @return \Ekyna\Component\Resource\Doctrine\ORM\Operator\ResourceOperator
     */
    protected function getOperator()
    {
        return $this->get('ekyna_product.catalog.operator');
    }
}
