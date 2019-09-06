<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductReferenceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class ProductReferenceRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReferenceRepository extends ResourceRepository implements ProductReferenceRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByTypeAndCode(string $type, string $code): ?ProductReferenceInterface
    {
        ProductReferenceTypes::isValid($type);

        if (empty($code)) {
            return null;
        }

        $qb = $this->createQueryBuilder('r');
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->eq('r.type', ':type'))
            ->andWhere($ex->eq('r.code', ':code'))
            ->setMaxResults(1)
            ->getQuery()
            ->setParameters([
                'type' => $type,
                'code' => $code,
            ])
            ->getOneOrNullResult();
    }
}
