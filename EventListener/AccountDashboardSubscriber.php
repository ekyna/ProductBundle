<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\PricingRepositoryInterface;
use Ekyna\Bundle\UserBundle\Event\DashboardEvent;
use Ekyna\Bundle\UserBundle\Model\DashboardWidget;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AccountDashboardSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountDashboardSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * @var PricingRepositoryInterface
     */
    private $pricingRepository;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface   $contextProvider
     * @param PricingRepositoryInterface $pricingRepository
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        PricingRepositoryInterface $pricingRepository
    ) {
        $this->contextProvider = $contextProvider;
        $this->pricingRepository = $pricingRepository;
    }

    /**
     * Account dashboard event handler.
     *
     * @param DashboardEvent $event
     */
    public function onDashboard(DashboardEvent $event)
    {
        $context = $this->contextProvider->getContext();

        if (empty($pricings = $this->pricingRepository->findByContext($context))) {
            return;
        }

        $scalars = [];
        foreach ($pricings as $pricing) {
            $scalars[] = $this->scalarPricing($pricing);
        }

        $widget = new DashboardWidget(
            'ekyna_product.account.pricing.title',
            'EkynaProductBundle:Account/Dashboard:pricings.html.twig',
            'default'
        );
        $widget
            ->setParameters([
                'pricings' => $scalars,
            ])
            ->setPriority(2000);

        $event->addWidget($widget);
    }

    /**
     * Builds the pricing scalar data.
     *
     * @param Model\PricingInterface $pricing
     *
     * @return array
     */
    private function scalarPricing(Model\PricingInterface $pricing)
    {
        $scalarRules = [];
        $previousMin = null;

        $rules = array_reverse($pricing->getRules()->toArray());
        /** @var Model\PricingRuleInterface $rule */
        foreach ($rules as $rule) {
            $scalarRules[] = [
                'min'     => $rule->getMinQuantity(),
                'max'     => $previousMin,
                'percent' => round($rule->getPercent(), 2),
            ];
            $previousMin = $rule->getMinQuantity() - 1;
        }

        $brands = array_map(function(Model\BrandInterface $brand) {
            return $brand->getTitle();
        }, $pricing->getBrands()->toArray());

        return [
            'brands' => $brands,
            'rules'  => array_reverse($scalarRules),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            DashboardEvent::DASHBOARD => ['onDashboard', 0],
        ];
    }
}
