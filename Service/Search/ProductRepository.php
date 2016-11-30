<?php

namespace Ekyna\Bundle\ProductBundle\Service\Search;

use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderAwareInterface;
use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderAwareTrait;
use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\CommerceBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends ResourceRepository implements LocaleProviderAwareInterface
{
    use LocaleProviderAwareTrait;

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
