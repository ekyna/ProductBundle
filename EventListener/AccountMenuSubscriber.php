<?php

declare(strict_types=1);

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
    protected CustomerProviderInterface $customerProvider;
    protected array                     $config;

    public function __construct(CustomerProviderInterface $customerProvider, array $config = [])
    {
        $this->customerProvider = $customerProvider;
        $this->config = array_replace([
            'catalog' => false,
        ], $config);
    }

    public function onMenuConfigure(MenuEvent $event): void
    {
        $menu = $event->getMenu();

        if (!$customer = $this->customerProvider->getCustomer()) {
            return;
        }

        // TODO per group access rules

        // Tickets
        if (!$this->config['catalog']) {
            return;
        }

        $menu
            ->addChild('account.catalog.title', [
                'route' => 'ekyna_product_account_catalog_index',
            ])
            ->setExtra('translation_domain', 'EkynaProduct');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MenuEvent::CONFIGURE_ACCOUNT => ['onMenuConfigure', -1], // After commerce menu event subscriber
        ];
    }
}
