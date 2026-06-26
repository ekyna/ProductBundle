<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceGridGuesser;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\View\LineView;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

use function is_null;

/**
 * Class QuoteViewType
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteViewType extends AbstractViewType
{
    public function __construct(
        private readonly PriceGridGuesser $priceGridGuesser,
    ) {

    }

    public function buildItemView(SaleItemInterface $item, LineView $view, array $options): void
    {
        if (!$options['private']) {
            return;
        }

        if (!$item->hasSubjectIdentity()) {
            return;
        }

        if (null === $group = $item->getRootSale()->getCustomerGroup()) {
            return;
        }

        $subject = $this->resolveItemSubject($item);

        if (!$subject instanceof ProductInterface) {
            return;
        }

        $guessed = $this->priceGridGuesser->guess($subject, $group, $item->getTotalQuantity());

        if (is_null($guessed) || $guessed->equals($item->getNetPrice())) {
            return;
        }

        $view->setClass('unit', 'warning');
    }

    public function supportsSale(SaleInterface $sale): bool
    {
        return $sale instanceof QuoteInterface;
    }

    public function getName(): string
    {
        return 'ekyna_product_quote';
    }
}
