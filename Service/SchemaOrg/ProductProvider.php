<?php

namespace Ekyna\Bundle\ProductBundle\Service\SchemaOrg;

use Ekyna\Bundle\CmsBundle\Event\SchemaOrgEvent;
use Ekyna\Bundle\CmsBundle\Service\SchemaOrg;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Spatie\SchemaOrg\Schema;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\ProductBundle\Service\SchemaOrg
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SchemaOrg\ProviderInterface, SchemaOrg\BuilderAwareInterface
{
    use SchemaOrg\BuilderAwareTrait;

    /**
     * @var ResourceEventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param ResourceEventDispatcherInterface $dispatcher
     * @param string                           $currency
     */
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
    public function build($object)
    {
        /** @var \Spatie\SchemaOrg\Brand $brand */
        $brand = $this->schemaBuilder->build($object->getBrand());

        $description = trim(strip_tags($object->getDescription()));

        if ($object->getType() === Model\ProductTypes::TYPE_VARIANT) {
            if (empty($description)) {
                $description = trim(strip_tags($object->getParent()->getDescription()));
            }
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
                        ->value((string)round($weight * 1000))
                        ->unitCode('GRM')
                );
            } else {
                $schema->weight(
                    Schema::quantitativeValue()
                        ->value((string)round($weight, 3))
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

        if (!($object->isQuoteOnly() || $object->isEndOfLife())) {
            $schema->offers(
                Schema::offer()
                    ->availability($this->getAvailability($object->getStockState()))
                    ->itemCondition('http://schema.org/NewCondition')
                    ->price((string)round($object->getNetPrice(), 2))// TODO Round regarding to currency
                    // TODO ->priceValidUntil()
                    ->priceCurrency($this->defaultCurrency)
                    // TODO ->seller()
            );
        }

        /** @var \Ekyna\Bundle\MediaBundle\Model\MediaInterface $image */
        if ($image = $object->getImages(true, 1)->first()) {
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

        $this->dispatcher->dispatch(ProductEvents::SCHEMA_ORG, $event);

        return $schema;
    }

    /**
     * @param $state
     *
     * @return string
     */
    private function getAvailability($state)
    {
        // TODO use SchemaOrg/ItemAvailability constants/enumerations when available.

        switch ($state) {
            case StockSubjectStates::STATE_PRE_ORDER:
                return 'http://schema.org/PreOrder';
            case StockSubjectStates::STATE_IN_STOCK:
                return 'http://schema.org/InStock';
            default:
                return 'http://schema.org/OutOfStock';
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($object)
    {
        return $object instanceof Model\ProductInterface
            && $object->getType() !== Model\ProductTypes::TYPE_CONFIGURABLE;
    }
}