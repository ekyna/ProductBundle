<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductSearchType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductSearchType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'resource'     => 'ekyna_product.product',
                'search_route' => function (Options $options) {
                    return $options['admin_mode']
                        ? 'api_ekyna_product_product_search'
                        : 'ekyna_product_account_product_search';
                },
                'required'     => true,
                'visible'      => false,
                'types'        => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIABLE,
                    ProductTypes::TYPE_BUNDLE,
                    ProductTypes::TYPE_CONFIGURABLE,
                ],
            ])
            ->setAllowedTypes('visible', 'bool')
            ->setAllowedTypes('types', 'array')
            ->setNormalizer('search_parameters', function (Options $options, $value) {
                if (!isset($value['types'])) {
                    $value['types'] = $options['types'];
                }

                if (!isset($value['visible']) && $options['visible']) {
                    $value['visible'] = 1;
                }

                return $value;
            });
    }

    public function getParent(): ?string
    {
        return ResourceSearchType::class;
    }
}

