<?php

namespace Ekyna\Bundle\ProductBundle\Service\Search;

use Ekyna\Component\Resource\Locale;
use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Elastica\Query;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\ProductBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends ResourceRepository implements Locale\LocaleProviderAwareInterface
{
    use Locale\LocaleProviderAwareTrait;

    /**
     * Search products having the given types.
     *
     * @param string       $expression
     * @param string|array $types
     * @param int          $limit
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\ProductInterface[]
     */
    public function searchByTypes($expression, $types, $limit = 10)
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        $boolQuery = new Query\BoolQuery();

        $locale = $this->localeProvider->getCurrentLocale();

        $matchQuery = new Query\MultiMatch();
        $matchQuery->setQuery($expression)->setFields([
            'designation^5',
            'reference',
            'translations.' . $locale . '.title',
            'brand.name',
        ]);
        $boolQuery->addMust($matchQuery);

        $typesQuery = new Query\Terms();
        $typesQuery->setTerms('type', $types);
        $boolQuery->addMust($typesQuery);

        return $this->find($boolQuery, $limit);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields()
    {
        $locale = $this->localeProvider->getCurrentLocale();

        return [
            'designation^6',
            'reference^4',
            'translations.' . $locale . '.title^2',
            'references',
            'brand.name',
            'categories.name',
            'translations.' . $locale . '.description',
            'seo.translations.' . $locale . '.title',
            'seo.translations.' . $locale . '.description',
        ];
    }
}
