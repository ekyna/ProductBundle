<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockAnalysis;

use Ekyna\Bundle\CommerceBundle\Service\Mailer\AddressHelper;
use Ekyna\Bundle\ProductBundle\Service\Stock\Analysis\Importer;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\File;
use Twig\Environment;

use function ini_set;
use function pathinfo;
use function Symfony\Component\Translation\t;
use function sys_get_temp_dir;
use function uniqid;

use const DIRECTORY_SEPARATOR;
use const PATHINFO_FILENAME;

/**
 * Class ImportController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockAnalysis
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ImportController
{
    public function __construct(
        private readonly Importer             $importer,
        private readonly FormFactoryInterface $formFactory,
        private readonly FlashHelper          $flashHelper,
        private readonly Environment          $twig,
        private readonly MailerInterface      $mailer,
        private readonly AddressHelper        $addressHelper,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this
            ->formFactory
            ->createBuilder(FormType::class, [
                'dry' => true,
            ])
            ->add('file', FileType::class, [
                'constraints' => [
                    new File([
                        'maxSize'          => '4096k',
                        'mimeTypes'        => [
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Excel document',
                    ]),
                ],
            ])
            ->add('dry', CheckboxType::class, [
                'label'    => 'Dry run',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->getForm();

        FormUtil::addFooter($form, [
            'submit_label' => t('button.import', [], 'EkynaUi'),
            'submit_icon'  => 'import',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $dry = $form->get('dry')->getData();

            try {
                return $this->import($file, $dry);
            } catch (Exception $e) {
                $this->flashHelper->addFlash($e->getMessage(), 'danger');
            }
        }

        $content = $this->twig->render('@EkynaProduct/Admin/StockAnalysis/import.html.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }

    private function import(UploadedFile $file, bool $dryRun): Response
    {
        ini_set('max_execution_time', '0');

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = (new AsciiSlugger())->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $directory = sys_get_temp_dir();
        try {
            $file->move($directory, $newFilename);
        } catch (FileException) {
            // ... handle exception if something happens during file upload
        }

        $report = $this->importer->importXls($directory . DIRECTORY_SEPARATOR . $newFilename, $dryRun);

        $this->sendReport($report);

        $content = $this->twig->render('@EkynaProduct/Admin/StockAnalysis/result.html.twig', [
            'report' => $report,
        ]);

        return new Response($content);
    }

    private function sendReport(array $report): void
    {
        $content = $this->twig->render('@EkynaProduct/Email/analysis_import_report.html.twig', [
            'title'  => 'Stock analysis import report',
            'report' => $report,
        ]);

        $from = $this->addressHelper->getAdminHelper()->getNotificationSender();
        $to = $this->addressHelper->getPurchaseAddress();

        $message = new Email();
        $message->subject('Stock analysis import report');
        $message->html($content);
        $message->from($from);
        $message->to($to);

        $this->mailer->send($message);
    }
}
