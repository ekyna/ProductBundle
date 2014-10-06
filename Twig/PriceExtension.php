<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

/**
 * Class PriceExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PriceExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns a formated price
     *  
     * @param float $number
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * 
     * @return string
     */
    public function priceFilter($number, $decimals = 2, $decPoint = ',', $thousandsSep = ' ')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = $price . '&nbsp;€';

        return $price;
    }

    /**
     * {inheritdoc}
     */
    public function getName()
    {
    	return 'ekyna_price';
    }
}
