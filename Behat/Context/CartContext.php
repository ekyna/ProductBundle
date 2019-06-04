<?php

namespace Ekyna\Bundle\ProductBundle\Behat\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class CartContext
 * @package Ekyna\Bundle\ProductBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartContext extends RawMinkContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * @var int
     */
    private $defaultWaitTimeout = 7000;


    /**
     * Wait until the add to cart form is ready.
     *
     * @When /^(?:|I )wait until the add to cart form is ready$/
     */
    public function waitForSaleItemConfigureFormReady()
    {
        $this->getJavascriptDriver()->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && (1 === jQuery('#sale_item_configure_pricing:visible').not(':empty').size()) 
EOT
        );
    }

    /**
     * Select a configurable product slot choice
     *
     * @param int $choice
     * @param int $slot
     *
     * @When /^(?:|I )select the choice "(?P<choice>[^"]+)" from the slot "(?P<slot>[^"]+)"$/
     */
    public function selectChoiceFromSlot($choice, $slot)
    {
        $choice--;
        $slot--;

        $this->getJavascriptDriver()->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && jQuery('.bundle-slot').eq($slot).find('.slot-buttons > li').eq($choice).find('label').click() 
EOT
        );
    }

    /**
     * @Given My cart is empty
     */
    public function clearCart()
    {
        $this
            ->getContainer()
            ->get('doctrine.dbal.default_connection')
            ->exec('DELETE FROM sf_dev.commerce_cart');
    }

    /**
     * Asserts that the driver supports javascript and returns it.
     *
     * @return \Behat\Mink\Driver\DriverInterface
     * @throws \Exception
     */
    private function getJavascriptDriver()
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof Selenium2Driver) {
            throw new \Exception('Unsupported driver');
        }

        return $driver;
    }
}
