<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use DateTime;
use Ekyna\Bundle\AdminBundle\Service\Mailer\MailerHelper;
use Ekyna\Bundle\ProductBundle\Service\Exporter\ProductSaleExporter;
use Ekyna\Component\Resource\Model\DateRange;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

use Throwable;

use function date;
use function file_get_contents;
use function sprintf;

/**
 * Class ProductSaleExportCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductSaleExportCommand extends Command
{
    protected static $defaultName = 'ekyna:product:export:product_sale';

    public function __construct(
        private readonly ProductSaleExporter $exporter,
        private readonly MailerHelper        $helper,
        private readonly MailerInterface     $mailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'The export start month')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'The export end month')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'The email to send the export to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $range = $this->createDateRange($input);

        $logger = new ConsoleLogger($output);

        $csv = $this->exporter->export($range, $logger);

        if (!empty($email = $input->getOption('email'))) {
            $to = [new Address($email)];
        } else {
            $to = $this->helper->getNotificationRecipients();
        }

        $message = new Email();
        $message
            ->from($this->helper->getNotificationSender())
            ->to(...$to)
            ->subject('Product sales export')
            ->html('See attached file');

        $message->attach(
            file_get_contents($csv->close()),
            sprintf(
                'sales_report_%s_%s.csv',
                $range->getStart()->format('Y-m-d'),
                $range->getEnd()->format('Y-m-d')
            ),
            'application/vnd.ms-excel'
        );

        $this->mailer->send($message);

        return Command::SUCCESS;
    }

    private function createDateRange(InputInterface $input): DateRange
    {
        if (!empty($date = $input->getOption('from'))) {
            try {
                $from = new DateTime($date);
            } catch (Throwable) {
                throw new Exception('Invalid «from» date.');
            }
        } else {
            $from = new DateTime(date('Y-m'));
        }

        if (!empty($date = $input->getOption('to'))) {
            try {
                $to = new DateTime($date);
            } catch (Throwable) {
                throw new Exception('Invalid «to» date.');
            }
        } else {
            $to = new DateTime();
        }

        return new DateRange($from, $to);
    }
}
