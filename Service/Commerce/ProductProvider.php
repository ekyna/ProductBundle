<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ApiBundle\Action\SearchAction;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Subject\Provider\AbstractSubjectProvider;

use function Symfony\Component\Translation\t;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider extends AbstractSubjectProvider
{
    public function getSearchActionAndParameters(string $context): array
    {
        if ($context === self::CONTEXT_ACCOUNT) {
            return [
                'route'      => 'ekyna_product_account_product_search',
                'parameters' => [],
            ];
        }

        $result = [
            'action'     => SearchAction::class,
            'parameters' => [],
        ];

        if ($context === self::CONTEXT_SUPPLIER) {
            $result['parameters'] = [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT,
                ],
            ];
        } elseif ($context === self::CONTEXT_ITEM) {
            $result['parameters'] = [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIABLE,
                    ProductTypes::TYPE_BUNDLE,
                    ProductTypes::TYPE_CONFIGURABLE,
                ],
            ];
        } elseif ($context === self::CONTEXT_SALE) {
            $result['parameters'] = [
                'types' => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT,
                    ProductTypes::TYPE_BUNDLE,
                    ProductTypes::TYPE_CONFIGURABLE,
                ],
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function getLabel()
    {
        return t('product.label.singular', [], 'EkynaProduct');
    }

    public static function getName(): string
    {
        return 'product';
    }
}
