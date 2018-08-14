<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariableUpdater;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariantUpdater;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractVariantHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractVariantHandler extends AbstractHandler
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var AttributeTypeRegistryInterface
     */
    protected $typeRegistry;

    /**
     * @var PriceCalculator
     */
    protected $priceCalculator;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var VariantUpdater
     */
    private $variantUpdater;

    /**
     * @var VariableUpdater
     */
    private $variableUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface     $persistenceHelper
     * @param LocaleProviderInterface        $localeProvider
     * @param AttributeTypeRegistryInterface $typeRegistry
     * @param PriceCalculator                $priceCalculator
     * @param ProductRepositoryInterface     $productRepository
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        LocaleProviderInterface $localeProvider,
        PriceCalculator $priceCalculator,
        AttributeTypeRegistryInterface $typeRegistry,
        ProductRepositoryInterface $productRepository
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->localeProvider = $localeProvider;
        $this->typeRegistry = $typeRegistry;
        $this->priceCalculator = $priceCalculator;

        $this->productRepository = $productRepository;
    }

    /**
     * Returns the variant updater.
     *
     * @return VariantUpdater
     */
    protected function getVariantUpdater()
    {
        if (null !== $this->variantUpdater) {
            return $this->variantUpdater;
        }

        return $this->variantUpdater = new VariantUpdater(
            $this->persistenceHelper,
            $this->localeProvider,
            $this->typeRegistry
        );
    }

    /**
     * Returns the variable updater.
     *
     * @return VariableUpdater
     */
    protected function getVariableUpdater()
    {
        if (null !== $this->variableUpdater) {
            return $this->variableUpdater;
        }

        return $this->variableUpdater = new VariableUpdater($this->priceCalculator);
    }
}
