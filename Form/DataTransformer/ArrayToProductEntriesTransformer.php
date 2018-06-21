<?php

namespace Ekyna\Bundle\ProductBundle\Form\DataTransformer;

use Ekyna\Bundle\ProductBundle\Model\ProductEntry;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ArrayToProductEntriesTransformer
 * @package AppBundle\Form\DataTransfomer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ArrayToProductEntriesTransformer implements DataTransformerInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $repository;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        if (empty($value)) {
            return [];
        }

        $entries = [];

        $position = 0;
        foreach ($value as $id) {
            if (null !== $product = $this->repository->find($id)) {
                $entry = new ProductEntry();
                $entry->setProduct($product);
                $entry->setPosition($position);

                $entries[] = $entry;
                $position++;
            }
        }

        return $entries;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return [];
        }

        $ids = [];

        /** @var ProductEntry $entry */
        foreach ($value as $entry) {
            if (null !== $product = $entry->getProduct()) {
                $ids[] = $product->getId();
            }
        }

        return $ids;
    }
}
