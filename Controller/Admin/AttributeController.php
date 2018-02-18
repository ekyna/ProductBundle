<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\Resource\SortableTrait;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;

/**
 * Class AttributeController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AttributeController extends ResourceController
{
    use SortableTrait;

    /**
     * @inheritdoc
     */
    protected function createNew(Context $context)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeInterface $resource */
        $resource = parent::createNew($context);

        $request = $context->getRequest();

        $registry = $this->get('ekyna_product.attribute.type_registry');

        $type = $request->attributes->get('type');
        if (!$registry->hasType($type)) {
            throw new \InvalidArgumentException("Unknown type '$type'.");
        }

        $resource->setType($type);

        return $resource;
    }

    /**
     * @inheritDoc
     */
    protected function buildShowData(
        /** @noinspection PhpUnusedParameterInspection */
        array &$data,
        /** @noinspection PhpUnusedParameterInspection */
        Context $context
    ) {
        /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeInterface $attribute */
        $attribute = $context->getResource();

        $attributeType = $this->get('ekyna_product.attribute.type_registry')->getType($attribute->getType());

        $tableType = $this->get('ekyna_product.attribute_choice.configuration')->getTableType();

        if ($attributeType->hasChoices()) {
            $table = $this
                ->getTableFactory()
                ->createTable('choices', $tableType, [
                    'source' => $attribute->getChoices()->toArray(),
                ]);

            if (null !== $response = $table->handleRequest($context->getRequest())) {
                return $response;
            }

            $data['choices'] = $table->createView();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function generateResourcePath($resource, $action = 'show', array $parameters = [])
    {
        if ($resource instanceof AttributeInterface && $action === 'new') {
            if (!array_key_exists('type', $parameters)) {
                $parameters['type'] = $resource->getType();
            }
        }

        return parent::generateResourcePath($resource, $action, $parameters);
    }
}
