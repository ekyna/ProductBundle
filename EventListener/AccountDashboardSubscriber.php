<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\PricingRepositoryInterface;
use Ekyna\Bundle\UserBundle\Event\DashboardEvent;
use Ekyna\Bundle\UserBundle\Model\DashboardWidget;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AccountDashboardSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountDashboardSubscriber implements EventSubscriberInterface
{
//    private ContextProviderInterface $contextProvider;
//    private PricingRepositoryInterface $pricingRepository;

//    public function __construct(
//        ContextProviderInterface $contextProvider,
//        PricingRepositoryInterface $pricingRepository
//    ) {
//        $this->contextProvider = $contextProvider;
//        $this->pricingRepository = $pricingRepository;
//    }

    public function onDashboard(DashboardEvent $event): void
    {
        /*$context = $this->contextProvider->getContext();

        if (empty($pricings = $this->pricingRepository->findByContext($context))) {
            return;
        }

        $scalars = [];
        foreach ($pricings as $pricing) {
            $scalars[] = $this->scalarPricing($pricing);
        }*/

        # TODO Display pricing groups when no brand is configured
        $widget = DashboardWidget::create('@EkynaProduct/Account/Dashboard/pricings.html.twig')
            ->setTitle(t('account.pricing.title', [], 'EkynaProduct'))
            /*->setParameters([
                'pricings' => $scalars,
            ])*/
            ->setPriority(950);

        $event->addWidget($widget);
    }

//    /**
//     * Builds the pricing scalar data.
//     */
//    private function scalarPricing(Model\PricingInterface $pricing): array
//    {
//        $scalarRules = [];
//        $previousMin = null;
//
//        $rules = array_reverse($pricing->getRules()->toArray());
//        /** @var Model\PricingRuleInterface $rule */
//        foreach ($rules as $rule) {
//            $scalarRules[] = [
//                'min'     => $rule->getMinQuantity(),
//                'max'     => $previousMin,
//                'percent' => $rule->getPercent()->toFixed(2),
//            ];
//            $previousMin = $rule->getMinQuantity() - 1;
//        }
//
//        $brands = array_map(function(Model\BrandInterface $brand) {
//            return $brand->getTitle();
//        }, $pricing->getBrands()->toArray());
//
//        return [
//            'brands' => $brands,
//            'rules'  => array_reverse($scalarRules),
//        ];
//    }

    public static function getSubscribedEvents(): array
    {
        return [
            DashboardEvent::class => ['onDashboard', 0],
        ];
    }
}
