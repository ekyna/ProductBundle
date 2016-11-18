<?php

namespace Ekyna\Bundle\ProductBundle\Table\Column;

use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
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
            'value' => $this->constantHelper->renderProductTypeBadge($cell->vars['value'], false),
        ]);

        /*$value = $cell->vars['value'];
        $cell->setVars([
            'type'       => 'boolean',
            'class'      => 'label-' . ProductTypes::getTheme($value),
            'label'      => $this->constantHelper->renderProductTypeLabel($value),
            'route'      => null,
            'parameters' => [],
        ]);*/
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_product_product_type';
    }
}
