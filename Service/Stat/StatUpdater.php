<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Entity\StatCross;
use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
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
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerGroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var bool
     */
    private $force = false;

    /**
     * @var \DateTime
     */
    private $maxUpdateDate;

    /**
     * @var StatCountRepository
     */
    private $countRepository;

    /**
     * @var StatCrossRepository
     */
    private $crossRepository;

    /**
     * @var StatCalculator
     */
    private $calculator;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Stopwatch
     */
    private $watch;

    /**
     * @var Group[]
     */
    private $groups;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface       $productRepository
     * @param CustomerGroupRepositoryInterface $groupRepository
     * @param EntityManagerInterface           $entityManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CustomerGroupRepositoryInterface $groupRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->productRepository = $productRepository;
        $this->groupRepository = $groupRepository;
        $this->entityManager = $entityManager;

        $this->calculator = new StatCalculator($entityManager->getConnection());
    }

    /**
     * Sets the output.
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Sets the whether to debug update.
     *
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * Sets whether to force update.
     *
     * @param bool $force
     */
    public function setForce(bool $force)
    {
        $this->force = $force;
    }

    /**
     * Sets the "max update" date time.
     *
     * @param \DateTime $date
     */
    public function setMaxUpdateDate(\DateTime $date)
    {
        $this->maxUpdateDate = $date;
    }

    /**
     * Purges the stats data.
     */
    public function purge()
    {
        $this
            ->entityManager
            ->getConnection()
            ->query("TRUNCATE TABLE product_stat_count; TRUNCATE TABLE product_stat_cross;");
    }

    /**
     * Updates the next product stats.
     *
     * @return float|int|null
     */
    public function updateNextProduct()
    {
        if (!$this->force && is_null($this->maxUpdateDate)) {
            throw new LogicException("You must set the max update date first.");
        }

        if (null === $this->product = $this->findNextProduct()) {
            return null;
        }

        $this->watch = new Stopwatch();
        $this->watch->start('product');

        $this->writeln("");
        $this->writeln('Updating <comment>' . $this->product->getFullDesignation() . '</comment> stats');

        $this->update();

        $this->product->setStatUpdatedAt(new \DateTime());

        $this->entityManager->persist($this->product);
        $this->entityManager->flush();

        $event = $this->watch->stop('product');

        $this->writeln("Product stats updated in <comment>{$event->getDuration()}ms</comment>");
        $this->writeln("");
        $this->writeln("-------------------------------");

        $this->entityManager->clear();

        $this->groups = null;

        return $event->getDuration();
    }

    /**
     * Updates the stats for the current product.
     */
    private function update()
    {
        $count = 0;
        foreach ($this->getGroups() as $group) {
            $this->writeln(" - Group <comment>{$group->getName()}</comment>");

            $orderDates = $this->calculator->getOrderDates($this->product, $group);
            $statDates = $this->calculator->getStatCountDates($this->product, $group);

            foreach ($orderDates as $date => $updated) {
                $this->write(sprintf(
                    '    - %s %s ',
                    $date,
                    str_pad('.', 16 - mb_strlen($date), '.', STR_PAD_LEFT)
                ));

                if (!$this->force && isset($statDates[$date]) && $statDates[$date] > $updated) {
                    $this->writeln('<comment>skipped</comment>');
                    continue;
                }

                $from = new \DateTime($date);
                $to = (clone $from)->modify('last day of this month')->setTime(23, 59, 59);

                $this->updateCount($group, $from, $to);
                $this->updateCross($group, $from, $to);

                $this->writeln('<info>updated</info>');

                $count++;
                if ($count % 10 === 0) {
                    $this->entityManager->flush();
                }
            }
        }

        if ($count % 10 !== 0) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param Group     $group
     * @param \DateTime $from
     * @param \DateTime $to
     */
    private function updateCount(Group $group, \DateTime $from, \DateTime $to)
    {
        // Count
        $quantity = $this->calculator->calculateCount($this->product, $group, $from, $to);

        // TODO Remove existing StatCount that do no longer match result

        $date = $from->format('Y-m');

        if (null === $stat = $this->getCountRepository()->findOne($this->product, $group, $date)) {
            $stat = new StatCount();
            $stat
                ->setProduct($this->product)
                ->setDate($date)
                ->setCustomerGroup($group);
        }

        $stat
            ->setCount($quantity)
            ->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($stat);
    }

    /**
     * @param Group     $group
     * @param \DateTime $from
     * @param \DateTime $to
     */
    private function updateCross(Group $group, \DateTime $from, \DateTime $to)
    {
        $data = $this->calculator->calculateCross($this->product, $group, $from, $to);

        // TODO Remove existing StatCross that do no longer match result

        $date = $from->format('Y-m');

        foreach ($data as $targetId => $quantity) {
            /** @var Product $target */
            $target = $this->entityManager->getReference($this->productRepository->getClassName(), $targetId);

            if (null === $stat = $this->getCrossRepository()->findOne($this->product, $target, $group, $date)) {
                $stat = new StatCross();
                $stat
                    ->setSource($this->product)
                    ->setTarget($target)
                    ->setDate($date)
                    ->setCustomerGroup($group);
            }

            $stat->setCount($quantity);

            $this->entityManager->persist($stat);
        }
    }

    /**
     * Returns the groups.
     *
     * @return Group[]
     */
    private function getGroups()
    {
        if ($this->groups) {
            return $this->groups;
        }

        $this->groups = (array)$this->groupRepository->findAll();

        return $this->groups;
    }

    /**
     * Returns the next product to update.
     *
     * @return Product|null
     */
    private function findNextProduct()
    {
        return $this->productRepository->findNextStatUpdate($this->maxUpdateDate);
    }

    /**
     * Returns the countRepository.
     *
     * @return StatCountRepository
     */
    private function getCountRepository()
    {
        if ($this->countRepository) {
            return $this->countRepository;
        }

        return $this->countRepository = $this->entityManager->getRepository(StatCount::class);
    }

    /**
     * Returns the crossRepository.
     *
     * @return StatCrossRepository
     */
    private function getCrossRepository()
    {
        if ($this->crossRepository) {
            return $this->crossRepository;
        }

        return $this->crossRepository = $this->entityManager->getRepository(StatCross::class);
    }

    /**
     * @param string $message
     */
    private function write(string $message)
    {
        if (!$this->debug || is_null($this->output)) {
            return;
        }

        $this->output->write($message);
    }

    /**
     * @param string $message
     */
    private function writeln(string $message)
    {
        if (!$this->debug || is_null($this->output)) {
            return;
        }

        $this->output->writeln($message);
    }
}
