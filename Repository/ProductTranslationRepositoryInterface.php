<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface ProductTranslationRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductTranslationRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds a duplicate translation.
     *
     * @param ProductTranslationInterface $translation
     *
     * @return ProductTranslationInterface|null
     */
    public function findDuplicate(ProductTranslationInterface $translation): ?ProductTranslationInterface;
}