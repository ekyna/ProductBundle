<?php

namespace Ekyna\Bundle\ProductBundle\Service\Search;

use Ekyna\Bundle\AdminBundle\Search\SearchRepositoryInterface;
use Elastica\Query;
use FOS\ElasticaBundle\Repository;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\CommerceBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends Repository implements SearchRepositoryInterface
{
    /**
     * Search products.
     *
     * @param string  $expression
     * @param integer $limit
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\ProductInterface[]
     */
    public function defaultSearch($expression, $limit = 10)
    {
        if (0 == strlen($expression)) {
            $query = new Query\MatchAll();
        } else {
            $query = new Query\MultiMatch();
            $query
                ->setQuery($expression)
                ->setFields([
                    'references^5',
                    'title^3',
                    'designation^3',
                    'brand.name',
                    'category.name',
                ]);
        }

        return $this->find($query, $limit);
    }
}
