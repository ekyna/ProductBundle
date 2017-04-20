<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Editor\Block;

use Ekyna\Bundle\CmsBundle\Editor\Adapter\AdapterInterface;
use Ekyna\Bundle\CmsBundle\Editor\Model\BlockInterface;
use Ekyna\Bundle\CmsBundle\Editor\Plugin\Block\AbstractPlugin;
use Ekyna\Bundle\CmsBundle\Editor\View\WidgetView;
use Ekyna\Bundle\ProductBundle\Form\Type\Editor\ProductSlideBlockType;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Twig\Environment;

/**
 * Class ProductSlidePlugin
 * @package Ekyna\Bundle\ProductBundle\Service\Editor\Block
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSlidePlugin extends AbstractPlugin
{
    const NAME = 'ekyna_product_slide';

    private ProductRepositoryInterface $repository;
    private Environment                $twig;

    public function __construct(
        ProductRepositoryInterface $repository,
        Environment                $twig,
        array                      $config
    ) {
        parent::__construct(array_replace([
            'template' => '@EkynaProduct/Editor/Block/product_slide.html.twig', // TODO configurable
        ], $config));

        $this->repository = $repository;
        $this->twig = $twig;
    }

    public function create(BlockInterface $block, array $data = []): void
    {
        parent::create($block, $data);

        $defaultData = [
            'max_width'   => '400px',
            'product_ids' => [],
        ];

        $block->setData(array_merge($defaultData, $data));
    }

    public function update(BlockInterface $block, Request $request, array $options = []): ?Response
    {
        $options = array_replace([
            'action' => $this->urlGenerator->generate('admin_ekyna_cms_editor_block_edit', [
                'blockId'         => $block->getId(),
                'widgetType'      => $request->get('widgetType', $block->getType()),
                '_content_locale' => $this->localeProvider->getCurrentLocale(),
            ]),
            'method' => 'post',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ], $options);

        $form = $this->formFactory->create(ProductSlideBlockType::class, $block->getData(), $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $block->setData($form->getData());

            return null;
        }

        return $this->createModalResponse('Modifier le bloc carousel produits.', $form->createView());
    }

    public function validate(BlockInterface $block, ExecutionContextInterface $context): void
    {
        // TODO: Implement validate() method.
    }

    public function createWidget(
        BlockInterface   $block,
        AdapterInterface $adapter,
        array            $options,
        int              $position = 0
    ): WidgetView {
        $data = $block->getData();

        $products = [];

        foreach ($data['product_ids'] as $productId) {
            if (null !== $product = $this->repository->findOneById($productId)) {
                $products[] = $product;
            }
        }

        $view = parent::createWidget($block, $adapter, $options, $position);
        $view->getAttributes()->addClass('product-slide');

        if (empty($products)) {
            $view->content = '<p>Edit this block to select products.</p>';
        } else {
            // TODO image source sets / imagine filters
            $view->content = $this->twig->render($this->config['template'], [
                'duration'  => isset($data['duration']) ? $data['duration'] : null,
                'max_width' => isset($data['max_width']) ? $data['max_width'] : null,
                'products'  => $products,
            ]);
        }

        return $view;
    }

    public function getTitle(): string
    {
        return 'Product carousel';
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getJavascriptFilePath(): string
    {
        return 'ekyna-product/editor/block/product-slide';
    }
}
