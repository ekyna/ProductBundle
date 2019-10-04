<?php

namespace Ekyna\Bundle\ProductBundle\Service\Search;

use Ekyna\Component\Resource\Locale;
use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;

/**
 * Class BrandRepository
 * @package Ekyna\Bundle\ProductBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BrandRepository extends ResourceRepository implements Locale\LocaleProviderAwareInterface
{
    use Locale\LocaleProviderAwareTrait;

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields(): array
    {
        $locale = $this->localeProvider->getCurrentLocale();

        return [
            'name^5',
            'translations.'.$locale.'.title^3',
            'seo.translations.'.$locale.'.title^3',
            'translations.'.$locale.'.description',
            'seo.translations.'.$locale.'.description',
        ];
    }
}
