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
     * Creates the search query.
     *
     * @param string $expression
     * @param array  $types
     *
     * @return Query
     */
    public function createSearchQuery(string $expression, array $types): Query
    {
        $match = new Query\MultiMatch();
        $match
            ->setQuery($expression)
            ->setFields($this->getDefaultMatchFields())
            ->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);

        if (empty($types)) {
            return Query::create($match);
        }

        $terms = new Query\Terms();
        $terms->setTerms('type', $types);

        $bool = new Query\BoolQuery();
        $bool
            ->addMust($match)
            ->addMust($terms);

        return Query::create($bool);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields(): array
    {
        $locale = $this->localeProvider->getCurrentLocale();

        return [
            'reference^4',
            'reference.analyzed',
            'references^3',
            'references.analyzed',
            'designation^3',
            'designation.analyzed',
            'translations.' . $locale . '.title^2',
            'translations.' . $locale . '.title.analyzed',
            'brand.name',
            'brand.name.analyzed',
        ];
    }
}
