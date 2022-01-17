<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\SchemaOrg;

use Ekyna\Bundle\CmsBundle\Event\SchemaOrgEvent;
use Ekyna\Bundle\CmsBundle\Service\SchemaOrg;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Spatie\SchemaOrg\Brand;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Type;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\ProductBundle\Service\SchemaOrg
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SchemaOrg\ProviderInterface, SchemaOrg\BuilderAwareInterface
{
    use SchemaOrg\BuilderAwareTrait;

    protected ResourceEventDispatcherInterface $dispatcher;
    protected string $defaultCurrency;

    public function __construct(ResourceEventDispatcherInterface $dispatcher, string $currency)
    {
        $this->dispatcher = $dispatcher;
        $this->defaultCurrency = $currency;
    }

    /**
     * @inheritDoc
     *
     * @param Model\ProductInterface $object
     */
    public function build(object $object): ?Type
    {
        /** @var Brand $brand */
        $brand = $this->schemaBuilder->build($object->getBrand());

        $description = trim(strip_tags($object->getDescription() ?? ''));

        if (empty($description) && ($object->getType() === Model\ProductTypes::TYPE_VARIANT)) {
            $description = trim(strip_tags($object->getParent()->getDescription() ?? ''));
        }

        $schema = Schema::product()
            ->brand($brand)
            ->name($object->getFullDesignation())
            ->sku($object->getReference());

        if (!empty($description)) {
            $schema->description($description);
        }
        if (0 < $weight = $object->getPackageWeight()) {
            if (1 > $weight) {
                $schema->weight(
                    Schema::quantitativeValue()
                        ->value($weight->mul(1000)->toFixed())
                        ->unitCode('GRM')
                );
            } else {
                $schema->weight(
                    Schema::quantitativeValue()
                        ->value($weight->toFixed(3))
                        ->unitCode('KGM')
                );
            }
        }

        if ($object->hasPackageDimensions()) {
            $schema
                ->width(Schema::quantitativeValue()->value($object->getPackageWidth())->unitCode('MMT'))
                ->height(Schema::quantitativeValue()->value($object->getPackageHeight())->unitCode('MMT'))
                ->depth(Schema::quantitativeValue()->value($object->getPackageDepth())->unitCode('MMT'));
        }

        if (null !== $date = $object->getReleasedAt()) {
            $schema->releaseDate($date);
        }

        if (null !== $ref = $object->getReferenceByType(Model\ProductReferenceTypes::TYPE_MANUFACTURER)) {
            $schema->mpn($ref);
        }
        if (null !== $ref = $object->getReferenceByType(Model\ProductReferenceTypes::TYPE_EAN_13)) {
            $schema->gtin13($ref);
        }
        if (null !== $ref = $object->getReferenceByType(Model\ProductReferenceTypes::TYPE_EAN_8)) {
            $schema->gtin8($ref);
        }

        $schema->offers(
            Schema::offer()
                ->availability($this->getAvailability($object))
                ->itemCondition('https://schema.org/NewCondition')
                ->price(Money::fixed($object->getMinPrice(), $this->defaultCurrency))
                // TODO ->priceValidUntil()
                ->priceCurrency($this->defaultCurrency)
        // TODO ->seller()
        );

        if ($image = $object->getImage()) {
            $schema->image($this->schemaBuilder->build($image));
        }

        /** @var Model\CategoryInterface $category */
        if ($category = $object->getCategories()->first()) {
            $parts = [];

            do {
                $parts[] = $category->getTitle();
            } while ($category = $category->getParent());

            $schema->category(implode(' > ', array_reverse($parts)));
        }

        // TODO ->url()

        // TODO ->isAccessoryOrSparePartFor()
        // TODO ->isConsumableFor()
        // TODO ->isRelatedTo()
        // TODO ->isSimilarTo()

        $event = new SchemaOrgEvent();
        $event
            ->setSchema($schema)
            ->setResource($object);

        $this->dispatcher->dispatch($event, ProductEvents::SCHEMA_ORG);

        return $schema;
    }

    /**
     * @param Model\ProductInterface $object
     *
     * @return string
     */
    private function getAvailability(Model\ProductInterface $object)
    {
        // TODO use SchemaOrg/ItemAvailability constants/enumerations when available.

        switch ($object->getStockState()) {
            case StockSubjectStates::STATE_PRE_ORDER:
                return 'https://schema.org/PreOrder';
            case StockSubjectStates::STATE_IN_STOCK:
                return 'https://schema.org/InStock';
            default:
                if ($object->isEndOfLife()) {
                    return 'https://schema.org/Discontinued';
                }

                return 'https://schema.org/OutOfStock';
        }
    }

    /**
     * @inheritDoc
     */
    public function supports(object $object): bool
    {
        return $object instanceof Model\ProductInterface
            && $object->getType() !== Model\ProductTypes::TYPE_CONFIGURABLE;
    }
}
