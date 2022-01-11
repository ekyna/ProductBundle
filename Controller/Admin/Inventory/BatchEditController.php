<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory;

use Ekyna\Bundle\ProductBundle\Form\Type\Inventory\BatchEditType;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function is_array;

/**
 * Class BatchEditController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BatchEditController extends AbstractController
{
    use InventoryTrait;

    private ResourceManagerInterface $productManager;
    private FormFactoryInterface     $formFactory;
    private UrlGeneratorInterface    $urlGenerator;
    private ValidatorInterface       $validator;

    public function __invoke(Request $request): Response
    {
        $this->assertXhr($request);

        $ids = $request->query->get('id');
        if (!is_array($ids) || empty($ids)) {
            return $this->respond([]);
        }

        $ids = array_map(fn($v) => (int)$v, $ids);

        $products = $this->findProductsById($ids);
        if (empty($products)) {
            return $this->respond([]);
        }

        $form = $this->formFactory->create(BatchEditType::class, null, [
            'action' => $this->urlGenerator->generate('admin_ekyna_product_inventory_batch_edit', [
                'id' => $ids,
            ]),
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $accessor = PropertyAccess::createPropertyAccessor();

            $error = false;
            foreach ($products as $product) {
                $fields = [
                    'stockMode',
                    'quoteOnly',
                    'endOfLife',
                    'stockFloor',
                    'replenishmentTime',
                    'minimumOrderQuantity',
                ];
                foreach ($fields as $field) {
                    if ($form->get($field . 'Chk')->getData()) {
                        $accessor->setValue($product, $field, $form->get($field)->getData());
                    }
                }

                $violations = $this->validator->validate($product, null, ['Default', $product->getType()]);
                if ($violations->count()) {
                    /** @var ConstraintViolationInterface $violation */
                    foreach ($violations as $violation) {
                        $form->addError(new FormError($violation->getMessage()));
                        $error = true;
                    }
                }
            }

            if (!$error) {
                foreach ($products as $product) {
                    $this->productManager->persist($product);
                }
                $this->productManager->flush();

                return $this->respond($ids);
            }
        }

        $modal = new Modal('inventory.button.batch_edit');
        $modal
            ->setDomain('EkynaProduct')
            ->setForm($form->createView())
            ->addButton(Modal::BTN_SUBMIT)
            ->addButton(Modal::BTN_CLOSE)
            ->setVars([
                'form_template' => '@EkynaProduct/Admin/Inventory/_batch_edit_form.html.twig',
                'products'      => $products,
            ]);

        return $this->modalRenderer->render($modal);
    }
}
