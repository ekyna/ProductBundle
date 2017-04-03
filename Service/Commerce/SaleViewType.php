<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Cart\Model as Cart;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;

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
    public function buildItemView(Common\SaleItemInterface $item, View\LineView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        if (null !== $subject = $this->resolveItemSubject($item, false)) {
            if (!$subject instanceof ProductInterface) {
                return;
            }

            $view->vars['link_path'] = $this->generateUrl('ekyna_product_product_admin_show', [
                'productId' => $subject->getId()
            ]);
            $view->vars['link_title'] = (string) $subject;
        }
    }

    /**
     * @inheritdoc
     */
    public function supportsSale(Common\SaleInterface $sale)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_product';
    }
}
