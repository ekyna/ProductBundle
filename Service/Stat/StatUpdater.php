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
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

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

        $this->updateCounts();

        $this->product->setStatUpdatedAt(new \DateTime());

        $this->entityManager->persist($this->product);
        $this->entityManager->flush();

        $event = $this->watch->stop('product');

        $this->writeln("Product stats updated in <comment>{$event->getDuration()}ms</comment>");
        $this->writeln("");
        $this->writeln("------------------------------");

        $this->entityManager->clear();

        $this->groups = null;

        return $event->getDuration();
    }

    /**
     * Updates the stat counts for the current product.
     */
    private function updateCounts()
    {
        if ($this->debug) {
            $this->watch->start('count');
        }
        $this->writeln('  Updating <comment>count</comment> stats');

        $count = 0;
        foreach ($this->getGroups() as $group) {
            $this->writeln("   - Group <comment>{$group->getName()}</comment>");

            $orderDates = $this->getOrderDates($group);
            $statDates = $this->getStatCountDates($group);

            foreach ($orderDates as $date => $updated) {
                $this->write(sprintf(
                    '      - %s %s ',
                    $date,
                    str_pad('.', 16 - mb_strlen($date), '.', STR_PAD_LEFT)
                ));

                if (!$this->force && isset($statDates[$date]) && $statDates[$date] > $updated) {
                    $this->writeln('<comment>skipped</comment>');
                    continue;
                }

                if (null === $stat = $this->getCountRepository()->findOne($this->product, $date, $group)) {
                    $stat = new StatCount();
                    $stat
                        ->setProduct($this->product)
                        ->setDate($date)
                        ->setCustomerGroup($group);
                }

                $from = new \DateTime($date);
                $to = (clone $from)->modify('last day of this month')->setTime(23,59,59);

                $quantity = $this->calculateCount($from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'), $group);

                $stat
                    ->setCount($quantity)
                    ->setUpdatedAt(new \DateTime());

                $this->entityManager->persist($stat);

                $this->writeln('<info>updated</info>');

                $count++;
                if ($count % 30 === 0) {
                    $this->entityManager->flush();
                }
            }
        }

        if ($count % 30 !== 0) {
            $this->entityManager->flush();
        }

        if ($this->debug) {
            $event = $this->watch->stop('count');
            $this->writeln("  Count stats updated in <comment>{$event->getDuration()}ms</comment>");
        }
    }

    /**
     * Calculates the sold quantity.
     *
     * @param string     $from
     * @param string     $to
     * @param Group $group
     *
     * @return int
     */
    private function calculateCount(string $from, string $to, Group $group)
    {
        $query =
            "SELECT SUM(q.q1 * q.q2 * q.q3 * q.q4 * q.q5) as quantity FROM (" .
            "  SELECT DISTINCT i1.id, " .
            "    i1.quantity as q1, " .
            "    IF(0 < i2.quantity, i2.quantity, 1) as q2, " .
            "    IF(0 < i3.quantity, i3.quantity, 1) as q3, " .
            "    IF(0 < i4.quantity, i4.quantity, 1) as q4, " .
            "    IF(0 < i5.quantity, i5.quantity, 1) as q5, " .
            "    IF(i1.order_id IS NOT NULL, i1.order_id, " .
            "       IF(i2.order_id IS NOT NULL, i2.order_id, " .
            "          IF(i3.order_id IS NOT NULL, i3.order_id, " .
            "             IF(i4.order_id IS NOT NULL, i4.order_id, i5.order_id)))) as order_id " .
            "  FROM commerce_order_item i1 " .
            "    LEFT JOIN commerce_order_item i2 ON i2.id = i1.parent_id " .
            "    LEFT JOIN commerce_order_item i3 ON i3.id = i2.parent_id " .
            "    LEFT JOIN commerce_order_item i4 ON i4.id = i3.parent_id " .
            "    LEFT JOIN commerce_order_item i5 ON i5.id = i4.parent_id " .
            "  WHERE i1.subject_provider = 'product' " .
            "    AND i1.subject_identifier = {$this->product->getIdentifier()} " .
            ") as q " .
            "JOIN commerce_order o ON q.order_id = o.id " .
            "WHERE o.state IN ('accepted', 'completed') " .
            "  AND o.is_sample=0 " .
            "  AND o.created_at BETWEEN '$from' AND '$to' " .
            " AND o.customer_group_id = {$group->getId()}";

        $result = $this->getConnection()->query($query);
        if (false !== $data = $result->fetch(\PDO::FETCH_ASSOC)) {
            return (int) $data['quantity'];
        }

        return 0;
    }

    /**
     * Returns the orders dates.
     *
     * @param Group $group
     *
     * @return array
     */
    private function getOrderDates(Group $group)
    {
        $query =
            "SELECT DATE_FORMAT(o.created_at, '%Y-%m') as date, MAX(o.updated_at) as updated_at FROM (" .
            "  SELECT" .
            "    IF(i1.order_id IS NOT NULL, i1.order_id," .
            "      IF(i2.order_id IS NOT NULL, i2.order_id," .
            "        IF(i3.order_id IS NOT NULL, i3.order_id, ".
            "          IF(i4.order_id IS NOT NULL, i4.order_id, i5.order_id)))) as order_id ".
            "  FROM commerce_order_item i1 " .
            "  LEFT JOIN commerce_order_item i2 ON i2.id = i1.parent_id " .
            "  LEFT JOIN commerce_order_item i3 ON i3.id = i2.parent_id " .
            "  LEFT JOIN commerce_order_item i4 ON i4.id = i3.parent_id " .
            "  LEFT JOIN commerce_order_item i5 ON i5.id = i4.parent_id " .
            "  WHERE i1.subject_provider = 'product' " .
            "    AND i1.subject_identifier = {$this->product->getIdentifier()} " .
            ") as i " .
            "JOIN commerce_order o ON i.order_id = o.id " .
            "WHERE o.state IN ('accepted', 'completed') " .
            "  AND o.is_sample=0 " .
            "  AND o.customer_group_id = {$group->getId()} " .
            "GROUP BY date;";
        // TODO and o.created_at > -1 year

        $result = $this->getConnection()->query($query);

        $dates = [];
        while (false !== $data = $result->fetch(\PDO::FETCH_ASSOC)) {
            $dates[$data['date']] = $data['updated_at'];
        }

        return $dates;
    }

    /**
     * Returns the stat count dates.
     *
     * @param Group|null $group
     *
     * @return array
     */
    private function getStatCountDates(Group $group)
    {
        $query =
            "SELECT s.date, s.updated_at " .
            "FROM product_stat_count s " .
            "WHERE product_id = {$this->product->getIdentifier()} " .
            "  AND s.group_id = {$group->getId()}";
        // TODO and s.date >= -1 year

        $result = $this->getConnection()->query($query);

        $dates = [];
        while (false !== $data = $result->fetch(\PDO::FETCH_ASSOC)) {
            $dates[$data['date']] = $data['updated_at'];
        }

        return $dates;
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
     * Returns the connection.
     *
     * @return \Doctrine\DBAL\Connection
     */
    private function getConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        return $this->connection = $this->entityManager->getConnection();
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
