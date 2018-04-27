<?php

namespace Ekyna\Bundle\ProductBundle\Service\SchemaOrg;

use Ekyna\Bundle\CmsBundle\Service\SchemaOrg;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Spatie\SchemaOrg\Schema;

/**
 * Class BrandProvider
 * @package Ekyna\Bundle\ProductBundle\Service\SchemaOrg
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandProvider implements SchemaOrg\ProviderInterface, SchemaOrg\BuilderAwareInterface
{
    use SchemaOrg\BuilderAwareTrait;

    /**
     * @inheritDoc
     *
     * @param BrandInterface $object
     */
    public function build($object)
    {
        $schema = Schema::brand()
            ->name($object->getName())
            ->description(strip_tags($object->getDescription()));
            // TODO ->url()

        if ($image = $object->getMedia()) {
            $schema->logo($this->schemaBuilder->build($image));
        }

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function supports($object)
    {
        return $object instanceof BrandInterface;
    }
}