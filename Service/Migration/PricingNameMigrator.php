<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Migration;

use Doctrine\DBAL\Connection;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Bundle\ProductBundle\Repository\PricingRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\SpecialOfferRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Generator\PricingNameGenerator;

/**
 * Class PricingNameMigrator
 * @package Ekyna\Bundle\ProductBundle\Service\Migration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PricingNameMigrator
{
    private PricingNameGenerator            $nameGenerator;
    private PricingRepositoryInterface      $pricingRepository;
    private SpecialOfferRepositoryInterface $specialOfferRepository;
    private Connection                      $connection;

    public function __construct(
        PricingNameGenerator            $nameGenerator,
        PricingRepositoryInterface      $pricingRepository,
        SpecialOfferRepositoryInterface $specialOfferRepository,
        Connection                      $connection
    ) {
        $this->nameGenerator = $nameGenerator;
        $this->pricingRepository = $pricingRepository;
        $this->specialOfferRepository = $specialOfferRepository;
        $this->connection = $connection;
    }

    public function migrate(): void
    {
        $this->connection->executeStatement('
            UPDATE product_pricing 
            SET name=NULL 
            WHERE product_id IS NOT NULL;
        ');
        $this->connection->executeStatement('
            UPDATE product_pricing 
            SET designation=name 
            WHERE name IS NOT NULL 
              AND designation IS NULL;
        ');

        $update = $this->connection->prepare('UPDATE product_pricing SET name=:name WHERE id=:id LIMIT 1');

        $pricings = $this->pricingRepository->findBy(['product' => null]);

        /** @var PricingInterface $pricing */
        foreach ($pricings as $pricing) {
            $name = $this->nameGenerator->generatePricingName($pricing);

            if ($name === $pricing->getName()) {
                continue;
            }

            $update->executeStatement([
                'name' => $name,
                'id'   => $pricing->getId(),
            ]);
        }


        $this->connection->executeStatement('
            UPDATE product_special_offer 
            SET name=NULL 
            WHERE product_id IS NOT NULL;
        ');
        $this->connection->executeStatement('
            UPDATE product_special_offer 
            SET designation=name 
            WHERE name IS NOT NULL 
              AND designation IS NULL;
        ');

        $update = $this->connection->prepare('UPDATE product_special_offer SET name=:name WHERE id=:id LIMIT 1');

        $specialOffers = $this->specialOfferRepository->findBy(['product' => null]);

        /** @var SpecialOfferInterface $specialOffer */
        foreach ($specialOffers as $specialOffer) {
            $name = $this->nameGenerator->generateSpecialOfferName($specialOffer);

            if ($name === $specialOffer->getName()) {
                continue;
            }

            $update->executeStatement([
                'name' => $name,
                'id'   => $specialOffer->getId(),
            ]);
        }
    }
}
