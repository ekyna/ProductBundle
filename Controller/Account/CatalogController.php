<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Controller\Account\CustomerTrait;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\CatalogRenderType;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\CatalogType;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Repository\CatalogRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function intval;
use function Symfony\Component\Translation\t;

/**
 * Class CatalogController
 * @package Ekyna\Bundle\ProductBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogController implements ControllerInterface
{
    use CustomerTrait;

    private CatalogRepositoryInterface $catalogRepository;
    private ResourceFactoryInterface   $catalogFactory;
    private ResourceManagerInterface   $catalogManager;
    private ContextProviderInterface   $contextProvider;
    private CatalogRenderer            $catalogRenderer;
    private FlashHelper                $flashHelper;
    private FormFactoryInterface       $formFactory;
    private UrlGeneratorInterface      $urlGenerator;
    private Environment                $twig;

    public function __construct(
        CatalogRepositoryInterface $catalogRepository,
        ResourceFactoryInterface   $catalogFactory,
        ResourceManagerInterface   $catalogManager,
        ContextProviderInterface   $contextProvider,
        CatalogRenderer            $catalogRenderer,
        FlashHelper                $flashHelper,
        FormFactoryInterface       $formFactory,
        UrlGeneratorInterface      $urlGenerator,
        Environment                $twig
    ) {
        $this->catalogRepository = $catalogRepository;
        $this->catalogFactory = $catalogFactory;
        $this->catalogManager = $catalogManager;
        $this->contextProvider = $contextProvider;
        $this->catalogRenderer = $catalogRenderer;
        $this->flashHelper = $flashHelper;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function index(): Response
    {
        $customer = $this->getCustomer();

        $catalogs = $this->catalogRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaProduct/Account/Catalog/index.html.twig', [
            'customer' => $customer,
            'catalogs' => $catalogs,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function create(Request $request): Response
    {
        $customer = $this->getCustomer();

        /** @var CatalogInterface $catalog */
        $catalog = $this->catalogFactory->create();
        $catalog
            ->setCustomer($customer)
            ->setTheme('default');

        $form = $this->formFactory->create(CatalogType::class, $catalog, [
            'action'   => $this->urlGenerator->generate('ekyna_product_account_catalog_create'),
            'method'   => 'POST',
            'customer' => true,
        ]);

        FormUtil::addFooter($form, [
            'cancel_path' => $this->urlGenerator->generate('ekyna_product_account_catalog_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->catalogManager->save($catalog);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(t('account.catalog.new.success', [], 'EkynaProduct'), 'success');

                $url = $this->urlGenerator->generate('ekyna_product_account_catalog_show', [
                    'catalogId' => $catalog->getId(),
                ]);

                return new RedirectResponse($url);
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $catalogs = $this->catalogRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaProduct/Account/Catalog/create.html.twig', [
            'customer' => $customer,
            'form'     => $form->createView(),
            'catalogs' => $catalogs,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function show(Request $request): Response
    {
        $customer = $this->getCustomer();

        $catalog = $this->findCatalog($request);

        $catalogs = $this->catalogRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaProduct/Account/Catalog/show.html.twig', [
            'customer' => $customer,
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function edit(Request $request): Response
    {
        $customer = $this->getCustomer();

        $catalog = $this->findCatalog($request);

        $form = $this->formFactory->create(CatalogType::class, $catalog, [
            'action'   => $this->urlGenerator->generate('ekyna_product_account_catalog_edit', [
                'catalogId' => $catalog->getId(),
            ]),
            'method'   => 'POST',
            'customer' => true,
        ]);

        FormUtil::addFooter($form, [
            'cancel_path' => $this->urlGenerator->generate('ekyna_product_account_catalog_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->catalogManager->save($catalog);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(t('account.catalog.edit.success', [], 'EkynaProduct'), 'success');

                $url = $this->urlGenerator->generate('ekyna_product_account_catalog_show', [
                    'catalogId' => $catalog->getId(),
                ]);

                return new RedirectResponse($url);
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $catalogs = $this->catalogRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaProduct/Account/Catalog/edit.html.twig', [
            'customer' => $customer,
            'catalog'  => $catalog,
            'form'     => $form->createView(),
            'catalogs' => $catalogs,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function remove(Request $request): Response
    {
        $customer = $this->getCustomer();

        $catalog = $this->findCatalog($request);

        // TODO Form

        $catalogs = $this->catalogRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaProduct/Account/Catalog/show.html.twig', [
            'customer' => $customer,
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function render(Request $request): Response
    {
        $customer = $this->getCustomer();

        $catalog = $this->findCatalog($request);

        $context = $this->contextProvider->getContext();

        $catalog
            ->setContext($context)
            ->setDisplayPrices(false)
            ->setFormat(CatalogRenderer::FORMAT_PDF);

        $cancelPath = $this->urlGenerator->generate('ekyna_product_account_catalog_show', [
            'catalogId' => $catalog->getId(),
        ]);

        // TODO not all fields

        $form = $this->formFactory->create(CatalogRenderType::class, $catalog, [
            'action' => $this->urlGenerator->generate('ekyna_product_account_catalog_print', [
                'catalogId' => $catalog->getId(),
            ]),
            'method' => 'POST',
        ]);

        FormUtil::addFooter($form, [
            'cancel_path'  => $cancelPath,
            'submit_label' => t('button.display', [], 'EkynaUi'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this->catalogRenderer->respond($catalog, $request);
            } catch (PdfException $e) {
                $this->flashHelper->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

                return new RedirectResponse($cancelPath);
            }
        }

        $catalogs = $this->catalogRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaProduct/Account/Catalog/print.html.twig', [
            'customer' => $customer,
            'catalog'  => $catalog,
            'form'     => $form->createView(),
            'catalogs' => $catalogs,
        ]);

        return (new Response($content))->setPrivate();
    }

    protected function findCatalog(Request $request): CatalogInterface
    {
        $customer = $this->getCustomer();

        $id = intval($request->attributes->get('catalogId'));

        $catalog = $this->catalogRepository->findOneByCustomerAndId($customer, $id);

        if (null === $catalog) {
            throw new NotFoundHttpException('Catalog not found.');
        }

        return $catalog;
    }
}
