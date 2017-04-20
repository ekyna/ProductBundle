<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Search;

use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Locale;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class CategoryRepository
 * @package Ekyna\Bundle\ProductBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryRepository extends SearchRepository implements Locale\LocaleProviderAwareInterface
{
    use Locale\LocaleProviderAwareTrait;

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

        return $result
            ->setTitle($source['text'])
            ->setIcon('fa fa-folder-open-o')
            ->setRoute('admin_ekyna_product_category_read') // TODO Use resource/action
            ->setParameters(['categoryId' => $source['id']]);
    }

    protected function getDefaultFields(): array
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
