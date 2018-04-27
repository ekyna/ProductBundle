<?php

namespace Ekyna\Bundle\ProductBundle\Service\SchemaOrg;

use Ekyna\Bundle\CmsBundle\Service\SchemaOrg;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
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
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $defaultCurrency
     */
    public function __construct(string $defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
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
        if (0 < $weight = $object->getWeight()) {
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

        if ($object->hasDimensions()) {
            $schema
                ->width(Schema::quantitativeValue()->value($object->getWidth())->unitCode('MMT'))
                ->height(Schema::quantitativeValue()->value($object->getHeight())->unitCode('MMT'))
                ->depth(Schema::quantitativeValue()->value($object->getDepth())->unitCode('MMT'));
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
                    ->priceCurrency($this->defaultCurrency)
            );
        }

        /** @var Model\ProductMediaInterface $media */
        if ($media = $object->getMedias([MediaTypes::IMAGE])->first()) {
            $schema->image($this->schemaBuilder->build($media->getMedia()));
        }

        /** @var Model\CategoryInterface $category */
        if ($category = $object->getCategories()->first()) {
            $parts = [];

            do  {
                $parts[] = $category->getTitle();
            } while ($category = $category->getParent());

            $schema->category(implode(' > ', array_reverse($parts)));
        }

        // TODO ->url()

        // TODO ->isAccessoryOrSparePartFor()
        // TODO ->isConsumableFor()
        // TODO ->isRelatedTo()
        // TODO ->isSimilarTo()

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