<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Form\Type\StockView\ResupplyType;
use Ekyna\Bundle\ProductBundle\Service\Stock\Resupply;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class ResupplyController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyController extends AbstractController
{
    use StockViewTrait;

    public function __construct(
        private readonly SupplierProductRepositoryInterface $supplierProductRepository,
        private readonly SupplierOrderRepositoryInterface   $supplierOrderRepository,
        private readonly Resupply                           $resupply,
        private readonly FormFactoryInterface               $formFactory,
        private readonly UrlGeneratorInterface              $urlGenerator,
        private readonly TranslatorInterface                $translator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->assertXhr($request);

        $product = $this->findProductById($id = $request->attributes->getInt('productId'));

        $form = $this->formFactory->create(ResupplyType::class, [], [
            'action'  => $this->urlGenerator->generate('admin_ekyna_product_stock_view_resupply', [
                'productId' => $id,
            ]),
            'product' => $product,
            'attr'    => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SupplierProductInterface $supplierProduct */
            $supplierProduct = $this
                ->supplierProductRepository
                ->find($request->request->getInt('supplierProduct'));

            if (null !== $supplierProduct) {
                /** @var SupplierOrderInterface $supplierOrder */
                $supplierOrder = null;
                if (0 < $supplierOrderId = $request->request->getInt('supplierOrder')) {
                    $supplierOrder = $this->supplierOrderRepository->find($supplierOrderId);
                }

                $quantity = $form->get('quantity')->getData();
                $netPrice = $form->get('netPrice')->getData();
                $estimatedDateOfArrival = $form->get('estimatedDateOfArrival')->getData();

                $supplierOrder = $this->resupply->resupply(
                    $supplierProduct,
                    $quantity,
                    $netPrice,
                    $supplierOrder,
                    $estimatedDateOfArrival
                );

                if (null === $supplierOrder) {
                    FormUtil::addErrorsFromResourceEvent($form, $this->resupply->getEvent());
                } else {
                    return $this->respond([$id]);
                }
            } else {
                $form->addError(new FormError('Veuillez choisir une référence fournisseur.'));
            }
        }

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->translator->trans('stock_view.modal.resupply', [], 'EkynaProduct'),
            $product->getFullDesignation(true)
        );

        $modal = new Modal($title);
        $modal
            ->setForm($form->createView())
            ->addButton(Modal::BTN_SUBMIT)
            ->addButton(Modal::BTN_CLOSE);

        return $this->modalRenderer->render($modal);
    }
}