<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Bundle\ProductBundle\Action\Admin\Sale\Item\SyncReferenceAction;
use Ekyna\Bundle\ProductBundle\Model\Permission;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\View\Action;
use Ekyna\Component\Commerce\Common\View\LineView;

/**
 * Class SaleViewType
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleViewType extends AbstractViewType
{
    public function buildItemView(SaleItemInterface $item, LineView $view, array $options): void
    {
        if (!$options['private'] && !$options['export']) {
            return;
        }

        if (!$item->hasSubjectIdentity()) {
            return;
        }

        $subject = $this->resolveItemSubject($item);

        if (!$subject instanceof ProductInterface) {
            return;
        }

        if ($options['export']) {
            $view->ean13 = $subject->getReferenceByType(ProductReferenceTypes::TYPE_EAN_13);
            $view->mpn = $subject->getReferenceByType(ProductReferenceTypes::TYPE_MANUFACTURER);
            $view->hsCode = $subject->getHsCode();
        }

        if (!$options['private']) {
            return;
        }

        // Sync reference action
        if (
            $subject->getReference() !== $item->getReference()
            && $this->resourceHelper->isGranted(Permission::SYNC_REFERENCE, $item)
        ) {
            $syncReferencePath = $this->resourceUrl($item, SyncReferenceAction::class);
            $view->addAction(new Action($syncReferencePath, 'fa fa-hashtag', [
                'title'           => $this->trans('sale_item.button.sync_reference', [], 'EkynaProduct'),
                'confirm'         => $this->trans('sale_item.confirm.sync_reference', [], 'EkynaProduct'),
                'data-sale-xhr' => null,
                //'data-sale-modal' => null,
                'class'           => 'text-danger',
            ]));
        }

        // Product summary
        $link = [
            'data-summary' => json_encode([
                'route'      => 'admin_ekyna_product_product_summary',
                'parameters' => ['productId' => $subject->getId()],
            ]),
        ];
        if (isset($view->vars['link'])) {
            $view->vars['link'] = array_replace($view->vars['link'], $link);
        } else {
            $view->vars['link'] = $link;
        }
    }

    public function supportsSale(SaleInterface $sale): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'ekyna_product_sale';
    }
}
