<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\View\LineView;

/**
 * Class SaleViewType
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleViewType extends AbstractViewType
{
    /**
     * @inheritDoc
     */
    public function buildItemView(SaleItemInterface $item, LineView $view, array $options)
    {
        if (!$options['private']) {
            return;
        }

        if (!$item->hasSubjectIdentity()) {
            return;
        }

        $subject = $this->resolveItemSubject($item);

        if (!$subject instanceof ProductInterface) {
            return;
        }

        $link = [
            'data-summary'  => json_encode([
                'route'      => 'ekyna_product_product_admin_summary',
                'parameters' => ['productId' => $subject->getId()],
            ])
        ];
        if (isset($view->vars['link'])) {
            $view->vars['link'] = array_replace($view->vars['link'], $link);
        } else {
            $view->vars['link'] = $link;
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsSale(SaleInterface $sale)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_product_sale';
    }
}