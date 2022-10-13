<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory;

use Ekyna\Bundle\ProductBundle\Form\Type\Inventory\QuickEditType;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class QuickEditController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuickEditController extends AbstractController
{
    use InventoryTrait;

    private ResourceManagerInterface $productManager;
    private FormFactoryInterface     $formFactory;
    private UrlGeneratorInterface    $urlGenerator;
    private TranslatorInterface      $translator;

    public function __construct(
        ResourceManagerInterface $productManager,
        FormFactoryInterface     $formFactory,
        UrlGeneratorInterface    $urlGenerator,
        TranslatorInterface      $translator
    ) {
        $this->productManager = $productManager;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public function __invoke(Request $request): Response
    {
        $this->assertXhr($request);

        $product = $this->findProductById($id = $request->attributes->getInt('productId'));

        $form = $this->formFactory->create(QuickEditType::class, $product, [
            'action' => $this->urlGenerator->generate('admin_ekyna_product_inventory_quick_edit', [
                'productId' => $id,
            ]),
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->productManager->update($product);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            } else {
                return $this->respond([$id]);
            }
        }

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->translator->trans('product.button.edit', [], 'EkynaProduct'),
            $product->getFullDesignation(true)
        );

        $modal = new Modal($title);
        $modal
            ->setForm($form->createView())
            ->addButton(Modal::BTN_SUBMIT)
            ->addButton(Modal::BTN_CLOSE)
            ->setVars([
                'form_template' => '@EkynaProduct/Admin/Inventory/_quick_edit_form.html.twig',
            ]);

        return $this->modalRenderer->render($modal);
    }
}
