<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Action\Admin\Product\GenerateReferenceAction;
use Ekyna\Bundle\ProductBundle\Model\OfferInterface;
use Ekyna\Bundle\ProductBundle\Model\PriceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\PriceRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Features;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_reverse;
use function sprintf;

/**
 * Class ProductReadHelper
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductReadHelper
{
    private Features                   $features;
    private RepositoryFactoryInterface $repositoryFactory;
    private ResourceHelper             $resourceHelper;
    private LocaleProviderInterface    $localeProvider;
    private TranslatorInterface        $translator;

    public function __construct(
        Features                   $features,
        RepositoryFactoryInterface $repositoryFactory,
        ResourceHelper             $resourceHelper,
        LocaleProviderInterface    $localeProvider,
        TranslatorInterface        $translator
    ) {
        $this->features = $features;
        $this->repositoryFactory = $repositoryFactory;
        $this->resourceHelper = $resourceHelper;
        $this->localeProvider = $localeProvider;
        $this->translator = $translator;
    }

    /**
     * @return array<ProductInterface>
     */
    public function getBundleParents(ProductInterface $bundle): array
    {
        return $this->getProductRepository()->findParentsByBundled($bundle);
    }

    /**
     * @return array<ProductInterface>
     */
    public function getOptionParents(ProductInterface $product): array
    {
        return $this->getProductRepository()->findParentsByOptionProduct($product);
    }

    /**
     * @return array<ProductInterface>
     */
    public function getComponentParents(ProductInterface $component): array
    {
        return $this->getProductRepository()->findParentsByComponent($component);
    }

    public function getOfferList(ProductInterface $product): array
    {
        $offers = $this
            ->getOfferRepository()
            ->findByProduct($product);

        return $this->buildList($offers, 'offers');
    }

    public function getPriceList(ProductInterface $product): array
    {
        $prices = $this
            ->getPriceRepository()
            ->findByProduct($product);

        return $this->buildList($prices, 'prices');
    }

    /**
     * @return array<array>
     */
    public function getMessages(ProductInterface $product): array
    {
        $messages = [];

        if ($message = $this->generatePendingOffersMessage($product)) {
            $messages[] = [$message, 'warning'];
        }

        if ($message = $this->generateGtin13Message($product)) {
            $messages[] = [$message, 'warning'];
        }

        return $messages;
    }

    private function generatePendingOffersMessage(ProductInterface $product): ?string
    {
        if (!($product->isPendingOffers() || $product->isPendingPrices())) {
            return null;
        }

        return $this->translator->trans('product.alert.pending_offers', [], 'EkynaProduct');
    }

    private function generateGtin13Message(ProductInterface $product): ?string
    {
        if (!$this->features->isEnabled(Features::GTIN13_GENERATOR)) {
            return null;
        }

        if (ProductTypes::isVariableType($product) || ProductTypes::isConfigurableType($product)) {
            return null;
        }

        if ($product->getReferenceByType(ProductReferenceTypes::TYPE_EAN_13)) {
            return null;
        }

        $url = $this->resourceHelper->generateResourcePath($product, GenerateReferenceAction::class, [
            'type' => ProductReferenceTypes::TYPE_EAN_13,
        ]);

        return $this->translator->trans('product.alert.generate_gtin_13', ['%url%' => $url], 'EkynaProduct');
    }

    /**
     * @param array<PriceInterface|OfferInterface> $objects
     */
    private function buildList(array $objects, string $name): array
    {
        $allGroups = $this->translator->trans('customer_group.message.all', [], 'EkynaCommerce');
        $allCountries = $this->translator->trans('country.message.all', [], 'EkynaCommerce');

        $list = [];
        foreach ($objects as $object) {
            $group = $object->getGroup();
            $country = $object->getCountry();

            $key = sprintf(
                '%d-%d',
                $group ? $group->getId() : 0,
                $country ? $country->getId() : 0
            );

            $locale = $this->localeProvider->getCurrentLocale();

            if (!isset($list[$key])) {
                $list[$key] = [
                    'title' => sprintf(
                        '%s / %s',
                        $group ? $group->getName() : $allGroups,
                        $country ? Countries::getName($country->getCode(), $locale) : $allCountries
                    ),
                    $name   => [],
                ];
            }

            $list[$key][$name][] = $object;
        }

        $list = array_reverse($list);

        foreach ($list as &$data) {
            $data[$name] = array_reverse($data[$name]);
        }

        return $list;
    }

    private function getProductRepository(): ProductRepositoryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repositoryFactory->getRepository(ProductInterface::class);
    }

    private function getOfferRepository(): OfferRepositoryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repositoryFactory->getRepository(OfferInterface::class);
    }

    private function getPriceRepository(): PriceRepositoryInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repositoryFactory->getRepository(PriceInterface::class);
    }
}
