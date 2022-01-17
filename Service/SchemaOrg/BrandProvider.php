<?php

namespace Ekyna\Bundle\ProductBundle\Service\SchemaOrg;

use Ekyna\Bundle\CmsBundle\Service\SchemaOrg;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Type;

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
     * @param object $object
     */
    public function build(object $object): ?Type
    {
        $schema = Schema::brand()
            ->name($object->getName())
            ->description(strip_tags($object->getDescription() ?? ''));
            // TODO ->url()

        if ($image = $object->getMedia()) {
            $schema->logo($this->schemaBuilder->build($image));
        }

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function supports(object $object): bool
    {
        return $object instanceof BrandInterface;
    }
}
