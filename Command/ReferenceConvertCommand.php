<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Doctrine\DBAL\Connection;
use Swift_Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReferenceConvertCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReferenceConvertCommand extends Command
{
    protected static $defaultName = 'ekyna:product:reference:convert';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $email;

    /**
     * Constructor.
     *
     * @param Connection   $connection
     * @param Swift_Mailer $mailer
     * @param string       $email
     */
    public function __construct(Connection $connection, Swift_Mailer $mailer, string $email)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->mailer = $mailer;
        $this->email = $email;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $update = $this->connection->prepare(<<<SQL
            UPDATE product_product AS p SET p.reference=:ref WHERE p.id=:id LIMIT 1
        SQL
        );

        $find = $this->connection->prepare(<<<SQL
            SELECT p.id FROM product_product AS p WHERE p.reference=:ref AND p.id!=:id LIMIT 1
        SQL
        );

        $select = $this->connection->executeQuery(<<<SQL
            SELECT p.id, p.designation, p.reference, p.attr_designation, pp.designation as parent_designation  
            FROM product_product AS p 
            LEFT JOIN product_product AS pp ON pp.id=p.parent_id
            ORDER BY id
        SQL
        );

        $this->connection->beginTransaction();

        $report = '';

        try {
            while (false !== $data = $select->fetch(\PDO::FETCH_ASSOC)) {
                $id = $data['id'];

                $oldRef = $data['reference'];

                $designation = empty($data['designation'])
                    ? $data['parent_designation'] . ' ' . $data['attr_designation']
                    : $data['designation'];

                $name = sprintf('[%d] <comment>%s</comment> ', $id, $designation);
                $length = mb_strlen(sprintf('[%d] %s ', $id, $designation));

                $output->write($name . str_pad('.', 80 - $length, '.', STR_PAD_LEFT));

                if (preg_match('~^[a-zA-Z0-9-_]+$~', $oldRef)) {
                    $output->writeln(' <comment>ok</comment>');

                    continue;
                }

                $newRef = preg_replace('~[^a-zA-Z0-9-_]~', '-', $oldRef);

                // Duplicate check
                $find->execute(['ref' => $newRef, 'id' => $id]);
                if (1 === $find->rowCount()) {
                    $output->writeln(' <error>error</error>');
                    throw new \Exception("Duplicate reference");
                }

                // Update new reference
                if (false === $update->execute(['ref' => $newRef, 'id' => $id])) {
                    $output->writeln(' <error>error</error>');
                    throw new \Exception("SQL update failed");
                } else {
                    $output->writeln(' <info>changed</info>');
                }

                $report .= sprintf("%s : %s\n", $oldRef, $newRef);
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }

        $this->connection->close();

        if (empty($report)) {
            return;
        }

        $output->writeln("");

        $message = \Swift_Message::newInstance('Product changed references', $report, 'text/plain')
            ->setFrom($this->email)
            ->setTo($this->email);

        $output->writeln("Sending email ... ");

        try {
            if (0 === $this->mailer->send($message)) {
                throw new \Exception();
            }
            $output->writeln("<info>success</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>failure</error>");
            $output->writeln($report);
        }
    }
}
