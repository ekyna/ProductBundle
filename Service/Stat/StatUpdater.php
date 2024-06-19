<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Entity\StatCross;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;
use Ekyna\Bundle\ProductBundle\Repository\StatCrossRepository;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class CountUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdater
{
    private ?OutputInterface $output = null;
    private bool             $debug  = false;
    private bool             $force  = false;

    private StatCalculator   $calculator;
    private ProductInterface $product;
    private ?array           $groups = null;


    public function __construct(
        private readonly StatCountRepository              $countRepository,
        private readonly StatCrossRepository              $crossRepository,
        private readonly ProductRepositoryInterface       $productRepository,
        private readonly CustomerGroupRepositoryInterface $groupRepository,
        private readonly EntityManagerInterface           $entityManager
    ) {
        $this->calculator = new StatCalculator($entityManager->getConnection());
    }

    /**
     * Sets the output.
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Sets whether to debug update.
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * Sets whether to force update.
     */
    public function setForce(bool $force): void
    {
        $this->force = $force;
    }

    /**
     * Purges the statistics data.
     */
    public function purge(): void
    {
        $connection = $this->entityManager->getConnection();

        $connection->executeQuery('TRUNCATE TABLE product_stat_count');
        $connection->executeQuery('TRUNCATE TABLE product_stat_cross');
    }

    /**
     * Updates the next product stats.
     *
     * @param ProductInterface $product
     * @return float|int|null
     */
    public function update(ProductInterface $product): float|int|null
    {
        $this->product = $product;

        $watch = new Stopwatch();
        $watch->start('product');

        $this->writeln('');
        $this->writeln('Updating <comment>' . $this->product->getFullDesignation() . '</comment> stats');

        $this->updateStats();

        $this->product->setStatUpdatedAt(new DateTime());

        $this->entityManager->persist($this->product);
        $this->entityManager->flush();

        $event = $watch->stop('product');

        $this->writeln("Product stats updated in <comment>{$event->getDuration()}ms</comment>");
        $this->writeln('');
        $this->writeln('-------------------------------');

        $this->entityManager->clear();
        $this->groups = null;

        return $event->getDuration();
    }

    /**
     * Updates the stats for the current product.
     */
    private function updateStats(): void
    {
        $count = 0;
        foreach (StatCount::getSources() as $source) {
            foreach ($this->getGroups() as $group) {
                $this->writeln(" - [$source] Group <comment>{$group->getName()}</comment>");

                $orderDates = $this->calculator->getSourceDates($this->product, $group, $source);
                $statDates = $this->calculator->getStatCountDates($this->product, $group, $source);

                foreach ($orderDates as $date => $updated) {
                    $this->write(
                        sprintf(
                            '    - %s %s ',
                            $date,
                            str_pad('.', 16 - mb_strlen($date), '.', STR_PAD_LEFT)
                        )
                    );

                    if (!$this->force && isset($statDates[$date]) && $statDates[$date] > $updated) {
                        $this->writeln('<comment>skipped</comment>');
                        continue;
                    }

                    $from = new DateTime($date);
                    $to = (clone $from)->modify('last day of this month')->setTime(23, 59, 59, 999999);

                    $this->updateCount($source, $group, $from, $to);
                    if ($source === StatCount::SOURCE_ORDER) {
                        $this->updateCross($group, $from, $to);
                    }

                    $this->writeln('<info>updated</info>');

                    $count++;
                    if ($count % 10 === 0) {
                        $this->entityManager->flush();
                    }
                }
            }

            if ($count % 10 === 0) {
                $this->entityManager->flush();
            }
        }

        if ($count % 10 !== 0) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param Group    $group
     * @param DateTime $from
     * @param DateTime $to
     * @param string   $source
     */
    private function updateCount(string $source, Group $group, DateTime $from, DateTime $to): void
    {
        // Count
        $quantity = $this
            ->calculator
            ->calculateCountByGroup($this->product, $source, $group, $from, $to);

        // TODO Remove existing StatCount that do no longer match result

        $date = $from->format('Y-m');

        if (null === $stat = $this->countRepository->findOne($this->product, $source, $group, $date)) {
            $stat = new StatCount();
            $stat
                ->setProduct($this->product)
                ->setSource($source)
                ->setDate($date)
                ->setCustomerGroup($group);
        } elseif ($quantity !== $previous = $stat->getCount()) {
            $this->writeln("<comment>$previous => $quantity</comment>");
        }

        $stat
            ->setCount($quantity)
            ->setUpdatedAt(new DateTime());

        $this->entityManager->persist($stat);
    }

    /**
     * @param Group    $group
     * @param DateTime $from
     * @param DateTime $to
     */
    private function updateCross(Group $group, DateTime $from, DateTime $to): void
    {
        $data = $this->calculator->calculateCrossByGroup($this->product, $group, $from, $to);

        // TODO Remove existing StatCross that do no longer match result

        $date = $from->format('Y-m');

        foreach ($data as $targetId => $quantity) {
            /** @var ProductInterface $target */
            $target = $this->entityManager->getReference($this->productRepository->getClassName(), $targetId);

            if (null === $stat = $this->crossRepository->findOne($this->product, $target, $group, $date)) {
                $stat = new StatCross();
                $stat
                    ->setSource($this->product)
                    ->setTarget($target)
                    ->setDate($date)
                    ->setCustomerGroup($group);
            } elseif ($quantity !== $previous = $stat->getCount()) {
                $this->writeln("<comment>$previous => $quantity</comment>");
            }

            $stat->setCount($quantity);

            $this->entityManager->persist($stat);
        }
    }

    /**
     * Returns the groups.
     *
     * @return array<Group>
     */
    private function getGroups(): array
    {
        if ($this->groups) {
            return $this->groups;
        }

        $this->groups = (array)$this->groupRepository->findAll();

        return $this->groups;
    }

    /**
     * @param string $message
     */
    private function write(string $message): void
    {
        if (!$this->debug || is_null($this->output)) {
            return;
        }

        $this->output->write($message);
    }

    /**
     * @param string $message
     */
    private function writeln(string $message): void
    {
        if (!$this->debug || is_null($this->output)) {
            return;
        }

        $this->output->writeln($message);
    }
}
