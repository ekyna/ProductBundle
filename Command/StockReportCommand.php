<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes as BStockModes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes as CStockModes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class StockReportCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockReportCommand extends Command
{
    protected static $defaultName = 'ekyna:product:stock:report';

    private ProductRepositoryInterface $repository;
    private Environment                $twig;
    private SettingManagerInterface    $settings;
    private TranslatorInterface        $translator;
    private MailerInterface            $mailer;


    public function __construct(
        ProductRepositoryInterface $repository,
        Environment                $twig,
        SettingManagerInterface    $settings,
        TranslatorInterface        $translator,
        MailerInterface            $mailer
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->twig = $twig;
        $this->settings = $settings;
        $this->translator = $translator;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this->setDescription('Generates a report about out of stock products.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modes = [
            CStockModes::MODE_AUTO,
            CStockModes::MODE_JUST_IN_TIME,
            CStockModes::MODE_MANUAL,
        ];

        foreach ($modes as $mode) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                strtoupper($mode),
                str_pad('.', 44 - mb_strlen($mode), '.', STR_PAD_LEFT)
            ));

            try {
                if ($this->sendReport($mode)) {
                    $output->writeln('<info>sent</info>');
                } else {
                    $output->writeln('none');
                }
            } catch (TransportExceptionInterface $e) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendReport(string $mode): bool
    {
        $products = $this->repository->findOutOfStockProducts($mode);

        if (empty($products)) {
            return false;
        }

        $title = $this->translator->trans('email.stock_report.title', [
            '%mode%' => BStockModes::getLabel($mode)->trans($this->translator),
        ], 'EkynaProduct');

        $body = $this->twig->render('@EkynaProduct/Email/stock_report.html.twig', [
            'title'    => $title,
            'mode'     => $mode,
            'products' => $products,
            'today'    => (new DateTime())->setTime(0, 0),
            'locale'   => $this->translator->getLocale(),
        ]);

        $fromEmail = $this->settings->getParameter('notification.from_email');
        $fromName = $this->settings->getParameter('notification.from_name');
        $toEmail = $this->settings->getParameter('notification.to_emails');

        $message = new Email();
        $message
            ->subject($title)
            ->from($fromEmail, $fromName)
            ->to($toEmail)
            ->html($body);

        $this->mailer->send($message);

        return true;
    }
}
