<?php

namespace Ekyna\Bundle\ProductBundle\Service\Editor\Block;

use Ekyna\Bundle\CmsBundle\Editor\Model\BlockInterface;
use Ekyna\Bundle\CmsBundle\Editor\Plugin\Block\AbstractPlugin;
use Ekyna\Bundle\ProductBundle\Form\Type\Editor\ProductSlideBlockType;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class ProductSlidePlugin
 * @package Ekyna\Bundle\ProductBundle\Service\Editor\Block
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSlidePlugin extends AbstractPlugin
{
    const NAME = 'ekyna_product_slide';

    /**
     * @var ProductRepositoryInterface
     */
    private $repository;

    /**
     * @var EngineInterface
     */
    private $templating;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     * @param EngineInterface            $templating
     * @param array                      $config TODO configurable template
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        EngineInterface $templating,
        array $config
    ) {
        parent::__construct(array_replace([
            'template' => '@EkynaProduct/Editor/Block/product_slide.html.twig',
        ], $config));

        $this->repository = $repository;
        $this->templating = $templating;
    }

    /**
     * @inheritDoc
     */
    public function create(BlockInterface $block, array $data = [])
    {
        parent::create($block, $data);

        $defaultData = [
            'product_ids' => [],
        ];

        $block->setData(array_merge($defaultData, $data));
    }

    /**
     * @inheritDoc
     */
    public function update(BlockInterface $block, Request $request, array $options = [])
    {
        $options = array_replace([
            'action' => $this->urlGenerator->generate('ekyna_cms_editor_block_edit', [
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

        return $this->createModal('Modifier le bloc carousel produits.', $form->createView());
    }

    /**
     * @inheritDoc
     */
    public function validate(BlockInterface $block, ExecutionContextInterface $context)
    {
        // TODO: Implement validate() method.
    }

    /**
     * @inheritDoc
     */
    public function createWidget(BlockInterface $block, array $options, $position = 0)
    {
        $data = $block->getData();

        $products = [];

        foreach ($data['product_ids'] as $productId) {
            if (null !== $product = $this->repository->findOneById($productId)) {
                $products[] = $product;
            }
        }

        $view = parent::createWidget($block, $options, $position);
        $view->getAttributes()->addClass('product-slide');

        if (empty($products)) {
            $view->content = '<p>Edit this block to select products.</p>';
        } else {
            $view->content = $this->templating->render(
                $this->config['template'],
                ['products' => $products]
            );
        }

        return $view;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return 'Product carousel';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getJavascriptFilePath()
    {
        return 'ekyna-product/editor/block/product-slide';
    }
}
