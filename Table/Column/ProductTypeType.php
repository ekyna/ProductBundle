<?php

namespace Ekyna\Bundle\ProductBundle\Table\Column;

use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Table;
use Ekyna\Component\Table\View\Cell;

/**
 * Class ProductTypeType
 * @package Ekyna\Bundle\ProductBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTypeType extends TextType
{
    /**
     * @var \Ekyna\Bundle\ProductBundle\Service\ConstantsHelper
     */
    private $constantHelper;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     */
    public function __construct(ConstantsHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildViewCell(Cell $cell, Table $table, array $options)
    {
        parent::buildViewCell($cell, $table, $options);

        $cell->setVars([
            'type'  => 'text',
            'value' => $this->constantHelper->renderProductTypeLabel($cell->vars['value']),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_product_product_type';
    }
}
