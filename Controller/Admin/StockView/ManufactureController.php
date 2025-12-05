<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Form\Type\StockView\ManufactureType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Manufacture\Repository\BillOfMaterialsRepositoryInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class ManufactureController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ManufactureController extends AbstractController
{
    use StockViewTrait;

    public function __construct(
        private readonly BillOfMaterialsRepositoryInterface $bomRepository,
        private readonly ResourceFactoryInterface           $productionOrderFactory,
        private readonly ResourceManagerInterface           $productionOrderManager,
        private readonly FormFactoryInterface               $formFactory,
        private readonly UrlGeneratorInterface              $urlGenerator,
        private readonly TranslatorInterface                $translator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->assertXhr($request);

        $product = $this->findProductById($id = $request->attributes->getInt('productId'));

        $bom = $this->bomRepository->findOneValidatedBySubject($product);

        /** @var ProductionOrderInterface $order */
        $order = $this->productionOrderFactory->create();
        $order->setBOM($bom);

        $form = $this->formFactory->create(ManufactureType::class, $order, [
            'action'  => $this->urlGenerator->generate('admin_ekyna_product_stock_view_manufacture', [
                'productId' => $id,
            ]),
            'product' => $product,
            'attr'    => [
                'class' => 'form-horizontal',
            ],
        ]);

        if (null === $bom) {
            $form->addError(new FormError("Cet article n'a aucune nomenclature disponible pour la fabrication."));
        } else {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $order->setQuantity($form->get('quantity')->getData());

                $event = $this->productionOrderManager->save($order);

                if ($event->hasErrors()) {
                    FormUtil::addErrorsFromResourceEvent($form, $event);
                } else {
                    return $this->respond([$id]);
                }
            }
        }

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->translator->trans('stock_view.modal.manufacture', [], 'EkynaProduct'),
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
