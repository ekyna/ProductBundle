<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Factory;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\TranslatableFactory;

/**
 * Class ProductFactory
 * @package Ekyna\Bundle\ProductBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductFactory extends TranslatableFactory implements ProductFactoryInterface
{
    protected StockSubjectUpdaterInterface $stockSubjectUpdater;

    public function __construct(StockSubjectUpdaterInterface $stockSubjectUpdater)
    {
        $this->stockSubjectUpdater = $stockSubjectUpdater;
    }

    public function createWithType(string $type): ProductInterface
    {
        $product = parent::create();

        if (!$product instanceof ProductInterface) {
            throw new UnexpectedValueException($product, ProductInterface::class);
        }

        ProductTypes::isValid($type, true);

        $product->setType($type);

        if (ProductTypes::isChildType($product)) {
            $this->stockSubjectUpdater->reset($product);
        }

        return $product;
    }
}
