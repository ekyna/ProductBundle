<?php

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes as BStockModes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes as CStockModes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class StockReportCommand
 * @package Ekyna\Bundle\ProductBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockReportCommand extends Command
{
    /**
     * @var ProductRepositoryInterface
     */
    private $repository;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var SettingsManagerInterface
     */
    private $settings;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     * @param EngineInterface            $templating
     * @param SettingsManagerInterface   $settings
     * @param TranslatorInterface        $translator
     * @param \Swift_Mailer              $mailer
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        EngineInterface $templating,
        SettingsManagerInterface $settings,
        TranslatorInterface $translator,
        \Swift_Mailer $mailer
    ) {
        $this->repository = $repository;
        $this->templating = $templating;
        $this->settings = $settings;
        $this->translator = $translator;
        $this->mailer = $mailer;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:product:stock:report')
            ->setDescription("Generates a report about out of stock products.");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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

            if ($this->sendReport($mode)) {
                $output->writeln('<info>sent</info>');
            } else {
                $output->writeln('none');
            }
        }
    }

    /**
     * Sends the report.
     *
     * @param string $mode
     *
     * @return bool
     */
    private function sendReport($mode)
    {
        $products = $this->repository->findOutOfStockProducts($mode);

        if (empty($products)) {
            return false;
        }

        $title = $this->translator->trans('ekyna_product.email.stock_report.title', [
            '%mode%' => $this->translator->trans(BStockModes::getLabel($mode)),
        ]);

        $body = $this->templating->render('@EkynaProduct/Email/stock_report.html.twig', [
            'title'    => $title,
            'mode'     => $mode,
            'products' => $products,
            'today'    => (new \DateTime())->setTime(0, 0, 0, 0),
            'locale'   => $this->translator->getLocale(),
        ]);

        $fromEmail = $this->settings->getParameter('notification.from_email');
        $fromName = $this->settings->getParameter('notification.from_name');
        $toEmail = $this->settings->getParameter('notification.to_emails');

        $message = new \Swift_Message();
        $message
            ->setSubject($title)
            ->setFrom($fromEmail, $fromName)
            ->setTo($toEmail)
            ->setBody($body, 'text/html');

        return 0 < $this->mailer->send($message);
    }
}
