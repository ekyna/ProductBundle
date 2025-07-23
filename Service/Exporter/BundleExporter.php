<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Exporter;

use Doctrine\Common\Collections\Collection;
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
use Ekyna\Component\Resource\Helper\File\AbstractFile;
use Ekyna\Component\Resource\Helper\File\Xls;

use function array_fill_keys;
use function array_merge;
use function sprintf;
use function str_repeat;

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

    public function export(ProductInterface $bundle, bool $recursive = false): AbstractFile
    {
        if (!ProductTypes::isBundleType($bundle)) {
            throw new UnexpectedValueException('Expected bundle product type.');
        }

        $file = new Xls(sprintf('%s_composition', $bundle->getReference()));

        $columns = [
            'designation',
            'reference',
            'type',
            'stock_state',
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

        $file->setHeaders($columns);

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

        $components = $this->build($bundle, $recursive);

        foreach ($components as $component) {
            $component = array_merge(array_fill_keys($columns, null), $component);

            if (0 < $component['level']) {
                $component['designation'] = str_repeat('│  ', $component['level']-1) . '└─ ' . $component['designation'];
            }

            if ($recursive && $component['type']['value'] === ProductTypes::TYPE_BUNDLE) {
                $component['type'] = $component['type']['label'];
            } else {
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
            }

            unset(
                $component['level'],
                $component['margin'],
                $component['choice'],
                $component['summary']
            );

            $file->addRow($component);
        }

        return $file;
    }

    public function build(ProductInterface $bundle, bool $recursive): array
    {
        return $this->list(
            $bundle->getBundleSlots(),
            $recursive
        );
    }

    private function list(Collection $slots, bool $recursive): array
    {
        $list = [];

        foreach ($slots as $slot) {
            /** @var BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();

            $product = $choice->getProduct();
            if (!($recursive && $product->getType() === ProductTypes::TYPE_BUNDLE)) {
                $list[] = $this->normalizeComponent($choice);

                continue;
            }

            $list[] = [
                'level'       => 0,
                'designation' => $product->getFullDesignation(true),
                'reference'   => $product->getReference(),
                'type'        => [
                    'value' => $product->getType(),
                    'label' => $this->productConstants->renderProductTypeLabel($product),
                    'badge' => $this->productConstants->renderProductTypeBadge($product),
                ],
            ];

            $children = $this->list($product->getBundleSlots(), true);

            foreach ($children as $child) {
                $child['level']++;
                if (isset($child['quantity'])) {
                    $child['quantity'] = $child['quantity']->mul($choice->getMinQuantity());
                }

                $list[] = $child;
            }
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
            'level'         => 0,
            'designation'   => $product->getFullDesignation(true),
            'reference'     => $product->getReference(),
            'type'          => [
                'value' => $product->getType(),
                'label' => $this->productConstants->renderProductTypeLabel($product),
                'badge' => $this->productConstants->renderProductTypeBadge($product),
            ],
            'stock_state'   => [
                'value' => $product->getStockState(),
                'label' => $this->commerceConstants->renderStockSubjectStateLabel($product),
                'badge' => $this->commerceConstants->renderStockSubjectStateBadge($product),
            ],
            'visible'       => [
                'value' => $product->isVisible(),
                'label' => $this->uiRenderer->renderBooleanLabel($product->isVisible()),
                'badge' => $this->uiRenderer->renderBooleanBadge($product->isVisible()),
            ],
            'hidden'        => [
                'value' => $choice->isHidden(),
                'label' => $this->uiRenderer->renderBooleanLabel($choice->isHidden(), $hiddenOptions),
                'badge' => $this->uiRenderer->renderBooleanBadge($choice->isHidden(), $hiddenOptions),
            ],
            'excludeImages' => [
                'value' => $choice->isExcludeImages(),
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
            'link'          => $this->resourceHelper->generateResourcePath($product, ReadAction::class, absolute: true),
            'summary'       => $this->resourceHelper->generateResourcePath($product, SummaryAction::class, absolute: true),
            'choice'        => $choice,
        ];
    }
}
