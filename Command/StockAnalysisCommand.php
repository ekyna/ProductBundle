<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\ProductBundle\Service\Stock\Analysis\Exporter;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

use function file_get_contents;

/**
 * Class StockReportCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAnalysisCommand extends Command
{
    protected static $defaultName = 'ekyna:product:stock:analysis';

    public function __construct(
        private readonly Exporter                $exporter,
        private readonly SettingManagerInterface $settings,
        private readonly TranslatorInterface     $translator,
        private readonly MailerInterface         $mailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Sends the stock analysis report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $path = $this->exporter->exportXls();
        } catch (Throwable) {
            $output->writeln('<info>Failed to generate analysis</info>');

            return Command::FAILURE;
        }

        $subject = $this->translator->trans('export.stock_analysis', [], 'EkynaProduct');

        $fromEmail = $this->settings->getParameter('notification.from_email');
        $fromName = $this->settings->getParameter('notification.from_name');
        $toEmail = $this->settings->getParameter('notification.to_emails');

        $message = new Email();
        $message->from(new Address($fromEmail, $fromName));
        $message->to(...$toEmail);
        $message->subject($subject);
        $message->text('See attachment');
        $message->attach(file_get_contents($path), 'stock-analysis.xls', 'text/csv');

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface) {
            $output->writeln('<info>Failed to send email</info>');

            return Command::FAILURE;
        }

        $output->writeln('<info>sent</info>');

        return Command::SUCCESS;
    }
}
