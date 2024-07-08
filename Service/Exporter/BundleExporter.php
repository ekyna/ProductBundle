<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Exporter;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper as CommerceConstantsHelper;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper as ProductConstantsHelper;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PurchaseCostCalculator;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\UiBundle\Service\UiRenderer;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Resource\Helper\File\Csv;
use Ekyna\Component\Resource\Helper\File\File;

use function array_fill_keys;
use function array_merge;
use function sprintf;

/**
 * Class BundleCompositionExporter
 * @package Ekyna\Bundle\ProductBundle\Service\Exporter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleExporter
{
    public function __construct(
        private readonly ResourceHelper          $resourceHelper,
        private readonly ProductConstantsHelper  $productConstants,
        private readonly CommerceConstantsHelper $commerceConstants,
        private readonly UiRenderer              $uiRenderer,
        private readonly PurchaseCostCalculator  $costCalculator,
    ) {
    }

    public function export(ProductInterface $bundle): File
    {
        if (!ProductTypes::isBundleType($bundle)) {
            throw new UnexpectedValueException('Expected bundle product type.');
        }

        $file = Csv::create(sprintf('%s_composition.csv', $bundle->getReference()));

        $columns = [
            'designation',
            'reference',
            'stock_state',
            'type',
            'visible',
            'hidden',
            'excludeImages',
            'options',
            'quantity',
            'weight',
            'sell_price',
            'sell_total',
            'cost_product',
            'cost_supply',
            'cost_total',
            'margin_amount',
            'margin_percent',
            'link',
        ];

        $file->addRow($columns);

        $badges = [
            'stock_state',
            'type',
            'visible',
            'hidden',
            'excludeImages',
        ];
        $decimals = [
            'quantity'     => 1,
            'weight'       => 3,
            'sell_price'   => 2,
            'cost_product' => 2,
            'cost_supply'  => 2,
        ];
        foreach ($this->buildList($bundle) as $component) {
            $component = array_merge(array_fill_keys($columns, null), $component);

            foreach ($badges as $field) {
                $component[$field] = $component[$field]['label'];
            }

            foreach ($decimals as $field => $precision) {
                $component[$field] = $component[$field]->toFixed($precision);
            }

            $component['sell_total'] = $component['sell_price'] * $component['quantity'];
            $component['cost_total'] = ($component['cost_product'] + $component['cost_supply']) * $component['quantity'];

            $component['margin_amount'] = $component['margin']->getTotal(false);
            $component['margin_percent'] = $component['margin']->getPercent(false);

            unset(
                $component['margin'],
                $component['choice'],
                $component['summary']
            );

            $file->addRow($component);
        }

        return $file;
    }

    public function buildList(ProductInterface $bundle): array
    {
        $list = [];

        foreach ($bundle->getBundleSlots() as $slot) {
            $choice = $slot->getChoices()->first();

            $list[] = $this->normalizeComponent($choice);
        }

        return $list;
    }

    private function normalizeComponent(BundleChoiceInterface $choice): array
    {
        $product = $choice->getProduct();

        $hiddenOptions = [
            true => ['class' => 'warning'],
            null => ['class' => 'default'],
        ];

        $price = $choice->getNetPrice() ?? $product->getNetPrice();

        $cost = $this->costCalculator->calculateMinPurchaseCost($product, false);

        $margin = new Margin();
        $margin->addRevenueProduct($price);
        $margin->addCost($cost);

        return [
            'designation'   => $product->getFullDesignation(true),
            'reference'     => $product->getReference(),
            'stock_state'   => [
                'label' => $this->commerceConstants->renderStockSubjectStateLabel($product),
                'badge' => $this->commerceConstants->renderStockSubjectStateBadge($product),
            ],
            'type'          => [
                'label' => $this->productConstants->renderProductTypeLabel($product),
                'badge' => $this->productConstants->renderProductTypeBadge($product),
            ],
            'visible'       => [
                'label' => $this->uiRenderer->renderBooleanLabel($product->isVisible()),
                'badge' => $this->uiRenderer->renderBooleanBadge($product->isVisible()),
            ],
            'hidden'        => [
                'label' => $this->uiRenderer->renderBooleanLabel($choice->isHidden(), $hiddenOptions),
                'badge' => $this->uiRenderer->renderBooleanBadge($choice->isHidden(), $hiddenOptions),
            ],
            'excludeImages' => [
                'label' => $this->uiRenderer->renderBooleanLabel($choice->isExcludeImages(), $hiddenOptions),
                'badge' => $this->uiRenderer->renderBooleanBadge($choice->isExcludeImages(), $hiddenOptions),
            ],
            'options'       => '', // TODO \Ekyna\Bundle\ProductBundle\Twig\ProductHelper::renderBundleChoiceOptionGroups
            'quantity'      => $choice->getMinQuantity(),
            'weight'        => $product->getWeight(),
            'sell_price'    => $price,
            'cost_product'  => $cost->getProduct(),
            'cost_supply'   => $cost->getSupply(),
            'margin'        => $margin,
            'link'          => $this->resourceHelper->generateResourcePath($product, ReadAction::class),
            'summary'       => $this->resourceHelper->generateResourcePath($product, SummaryAction::class),
            'choice'        => $choice,
        ];
    }
}
