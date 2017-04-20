<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Bundle\ProductBundle\Model\HighlightModes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class HighlightController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HighlightController
{
    private ProductRepositoryInterface $productRepository;
    private ResourceManagerInterface   $productManager;
    private MenuBuilder                $menuBuilder;
    private TranslatorInterface $translator;
    private ValidatorInterface         $validator;
    private Environment                $twig;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceManagerInterface   $productManager,
        MenuBuilder                $menuBuilder,
        TranslatorInterface $translator,
        ValidatorInterface         $validator,
        Environment                $twig
    ) {
        $this->productRepository = $productRepository;
        $this->productManager = $productManager;
        $this->menuBuilder = $menuBuilder;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->twig = $twig;
    }

    public function index(): Response
    {
        $this->menuBuilder->breadcrumbAppend([
            'name'         => 'ekyna_product_highlight',
            'label'        => 'highlight.title',
            'trans_domain' => 'EkynaProduct',
            'route'        => 'admin_ekyna_product_highlight_index',
        ]);

        $products = $this->productRepository->findForHighlight();

        $visibleChoices = [
            $this->translator->trans('value.no', [], 'EkynaUi')  => 0,
            $this->translator->trans('value.yes', [], 'EkynaUi') => 1,
        ];

        $highlightChoices = [];
        foreach (HighlightModes::getChoices() as $key => $value) {
            $highlightChoices[$this->translator->trans($key, [], 'EkynaProduct')] = $value;
        }

        $content = $this->twig->render('@EkynaProduct/Admin/Highlight/index.html.twig', [
            'products'          => $products,
            'visible_choices'   => $visibleChoices,
            'highlight_choices' => $highlightChoices,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function update(Request $request): Response
    {
        $productId = $request->attributes->getInt('productId');
        $property = $request->request->get('property');
        $value = $request->request->get('value');

        if (empty($productId) || empty($property) || is_null($value)) {
            throw new BadRequestHttpException('Id, property and message must be defined with non empty values.');
        }

        $product = $this->productRepository->find($productId);
        if (null === $product) {
            throw new NotFoundHttpException('Product not found.');
        }

        switch ($property) {
            case 'visible':
                $product->setVisible((bool)$value);
                break;
            case 'visibility':
                $product->setVisibility((int)$value);
                break;
            case 'bestSeller':
                if (!HighlightModes::isValid($value)) {
                    throw new BadRequestHttpException("Unexpected value '$value'.");
                }
                $product->setBestSeller($value);
                break;
            case 'crossSelling':
                if (!HighlightModes::isValid($value)) {
                    throw new BadRequestHttpException("Unexpected value '$value'.");
                }
                $product->setCrossSelling($value);
                break;
            default:
                throw new BadRequestHttpException("Unexpected property '$property'.");
        }

        $violations = $this->validator->validate($product, null, ['Default', $product->getType()]);
        if (0 < $violations->count()) {
            throw new BadRequestHttpException('Product is not valid.');
        }

        $this->productManager->persist($product);
        $this->productManager->flush();

        return new JsonResponse([
            'id'           => $product->getId(),
            'visible'      => $product->isVisible() ? '1' : '0',
            'visibility'   => $product->getVisibility(),
            'bestSeller'   => $product->getBestSeller(),
            'crossSelling' => $product->getCrossSelling(),
        ]);
    }
}
