<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Search;

use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Locale;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;
use Elastica\Query;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\ProductBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends SearchRepository implements Locale\LocaleProviderAwareInterface
{
    use Locale\LocaleProviderAwareTrait;

    protected function createQuery(Request $request): Query\AbstractQuery
    {
        $query = parent::createQuery($request);

        if (empty($types = $request->getParameter('types'))) {
            $types = [
                ProductTypes::TYPE_SIMPLE,
                ProductTypes::TYPE_VARIABLE,
                ProductTypes::TYPE_BUNDLE,
                ProductTypes::TYPE_CONFIGURABLE,
            ];
        }

        $bool = new Query\BoolQuery();
        $bool->addMust($query);
        $bool->addMust(new Query\Terms('type', $types));

        if ($request->isPrivate()) {
            return $bool;
        }

        $bool
            ->addMust((new Query\Term())->setTerm('visible', true))
            ->addMust((new Query\Term())->setTerm('quote_only', false))
            ->addMust((new Query\Term())->setTerm('end_of_life', false)); // TODO Not end_of_life & out_of_stock

        return $bool;
    }

    /**
     * @inheritDoc
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (!$request->isPrivate()) {
            return null;
        }

        if (!is_array($source)) {
            throw new UnexpectedTypeException($source, 'array');
        }

        $result = new Result();

        $reference = is_array($source['reference']) ? current($source['reference']) : $source['reference'];

        return $result
            ->setTitle(sprintf('[%s] %s', $reference, $source['text']))
            ->setIcon('fa fa-cube')
            ->setRoute('admin_ekyna_product_product_read') // TODO Use resource/action
            ->setParameters(['productId' => $source['id']]);
    }

    protected function getDefaultFields(): array
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

    public function supports(Request $request): bool
    {
        return !empty($request->getExpression()) && 0 < $request->getLimit();
    }
}
