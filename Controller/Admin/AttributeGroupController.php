<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\Resource\SortableTrait;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;

/**
 * Class AttributeGroupController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AttributeGroupController extends ResourceController
{
    use SortableTrait;

    /**
     * @inheritDoc
     */
    protected function buildShowData(
        /** @noinspection PhpUnusedParameterInspection */
        array &$data,
        /** @noinspection PhpUnusedParameterInspection */
        Context $context
    ) {
        /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeGroupInterface $attributeGroup */
        $attributeGroup = $context->getResource();

        $type = $this->get('ekyna_product.attribute.configuration')->getTableType();

        $table = $this
            ->getTableFactory()
            ->createTable('attributes', $type, [
                'source' => $attributeGroup->getAttributes()->toArray(),
            ]);

        if (null !== $response = $table->handleRequest($context->getRequest())) {
            return $response;
        }

        $data['attributes'] = $table->createView();

        return null;
    }
}
