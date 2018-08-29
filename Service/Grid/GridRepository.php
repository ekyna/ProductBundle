<?php

namespace Ekyna\Bundle\ProductBundle\Service\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;

/**
 * Class GridRepository
 * @package Ekyna\Bundle\ProductBundle\Service\Grid
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GridRepository
{
    /**
     * @var ContextProviderInterface
     */
    protected $contextProvider;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var string
     */
    protected $productClass;



    public function fetch()
    {
        $qb = $this->manager->createQueryBuilder();
        $qb
            ->from($this->productClass, 'o');


    }
}
