<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\UserBundle\Event\MenuEvent;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AccountMenuSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountMenuSubscriber implements EventSubscriberInterface
{
    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     *
     * @param CustomerProviderInterface $customerProvider
     * @param array                     $config
     */
    public function __construct(CustomerProviderInterface $customerProvider, array $config = [])
    {
        $this->customerProvider = $customerProvider;

        $this->config = array_replace([
            'catalog' => false,
        ], $config);
    }

    /**
     * Menu configure event handler.
     *
     * @param MenuEvent $event
     */
    public function onMenuConfigure(MenuEvent $event)
    {
        $menu = $event->getMenu();

        $customer = $this->customerProvider->getCustomer();

        // TODO per group access rules

        // Tickets
        if ($this->config['catalog']) {
            $menu->addChild('ekyna_product.account.catalog.title', [
                'route' => 'ekyna_product_account_catalog_index',
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            MenuEvent::CONFIGURE_ACCOUNT => ['onMenuConfigure', -1], // After commerce menu event subscriber
        ];
    }
}
