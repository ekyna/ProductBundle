<?php

namespace Ekyna\Bundle\ProductBundle\Service\Search;

use Ekyna\Component\Resource\Locale;
use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\ProductBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends ResourceRepository implements Locale\LocaleProviderAwareInterface
{
    use Locale\LocaleProviderAwareTrait;

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields()
    {
        $locale = $this->localeProvider->getCurrentLocale();

        return [
            'reference^5',
            'references^5',
            'designation^3',
            'translations.'.$locale.'.title^3',
            'seo.translations.'.$locale.'.title^3',
            'brand.name',
            'categories.name',
            'translations.'.$locale.'.description',
            'seo.translations.'.$locale.'.description',
        ];
    }
}
