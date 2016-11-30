<?php

namespace Ekyna\Bundle\ProductBundle\Service\Search;

use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderAwareInterface;
use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderAwareTrait;
use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;

/**
 * Class BrandRepository
 * @package Ekyna\Bundle\CommerceBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BrandRepository extends ResourceRepository implements LocaleProviderAwareInterface
{
    use LocaleProviderAwareTrait;

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields()
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
