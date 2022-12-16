<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Repository\InventoryProductRepository;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CountController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CountController extends AbstractProductController
{
    public function __construct(
        InventoryProductRepository             $repository,
        EntityManagerInterface                 $manager,
        SerializerInterface                    $serializer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly FormFactoryInterface  $formFactory,
        private readonly ModalRenderer         $modalRenderer,
    ) {
        parent::__construct($repository, $manager, $serializer);
    }

    public function __invoke(Request $request): Response
    {
        if (null === $product = $this->getProduct($request)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $form = $this->getForm($product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($product);
            $this->manager->flush();

            return $this->respond($product);
        }

        $modal = new Modal('Stock');
        $modal
            ->setForm($form->createView())
            ->addButton(Modal::BTN_SUBMIT)
            ->addButton(Modal::BTN_CLOSE)
            ->setSize(Modal::SIZE_SMALL)
            ->setVars([
                'product'       => $product,
                'form_template' => '@EkynaProduct/Inventory/_count_form.html.twig',
            ]);

        return $this->modalRenderer->render($modal);
    }

    private function getForm(InventoryProduct $product): FormInterface
    {
        return $this
            ->formFactory
            ->createBuilder(FormType::class, $product, [
                'method' => 'POST',
                'action' => $this->urlGenerator->generate('admin_ekyna_product_inventory_app_count', [
                    'id' => $product->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ])
            ->add('realStock', IntegerType::class, [
                'decimal'     => true,
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                ],
                'attr'        => [
                    'pattern' => '\d*',
                ],
            ])
            ->getForm();
    }
}
