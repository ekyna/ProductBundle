<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;

/**
 * Interface ProductTranslationRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductTranslationRepositoryInterface extends ObjectRepository
{
    /**
     * Finds a duplicate translation.
     */
    public function findDuplicate(ProductTranslationInterface $translation): ?ProductTranslationInterface;
}
