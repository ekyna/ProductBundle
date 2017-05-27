<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Resource as RC;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Table\Type\ProductType;

/**
 * Class ProductController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductController extends ResourceController
{
    use RC\TinymceTrait,
        RC\ToggleableTrait;

    /**
     * @inheritdoc
     */
    protected function createNew(Context $context)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $resource */
        $resource = parent::createNew($context);

        $request = $context->getRequest();

        $type = $request->attributes->get('type');
        if (!ProductTypes::isValid($type)) {
            throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
        }

        $resource->setType($type);

        if ($type === ProductTypes::TYPE_VARIANT) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $parent */
            $parent = $this->getRepository()->find($request->query->get('parent'));
            if (null === $parent || $parent->getType() !== ProductTypes::TYPE_VARIABLE) {
                throw new \InvalidArgumentException('Invalid parent.');
            }
            $resource->setParent($parent);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function createNewResourceForm(Context $context, $footer = true, array $options = [])
    {
        if (!array_key_exists('action', $options)) {
            $resource = $context->getResource();
            $parentId = $context->getRequest()->query->get('parent');
            $options['action'] = $this->generateResourcePath($resource, 'new', ['parent' => $parentId]);
        }

        return parent::createNewResourceForm($context, $footer, $options);
    }

    /**
     * @inheritdoc
     */
    protected function buildShowData(array &$data, Context $context)
    {
        /** @var ProductInterface $product */
        $product = $context->getResource();

        if ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            $table = $this
                ->getTableFactory()
                ->createTable('variants', ProductType::class, [
                    'variant_mode' => true,
                    'source' => $product->getVariants()->toArray(),
                ]);

            if (null !== $response = $table->handleRequest($context->getRequest())) {
                return $response;
            }

            $data['variants'] = $table->createView();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function generateResourcePath($resource, $action = 'show', array $parameters = [])
    {
        if ($resource instanceof ProductInterface && $action === 'new') {
            if (!array_key_exists('type', $parameters)) {
                $parameters['type'] = $resource->getType();
            }
        }

        return parent::generateResourcePath($resource, $action, $parameters);
    }
}
