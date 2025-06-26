<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CommerceBundle\Event\DocumentExtraEvent;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentAttributeHelper;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttachmentTypes;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use League\Flysystem\Filesystem;

/**
 * Class DocumentExtraListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentExtraListener
{
    private ?FilesystemHelper   $filesystemHelper = null;
    private ?DocumentExtraEvent $event            = null;
    private ?string             $locale           = null;

    public function __construct(
        private readonly DocumentAttributeHelper $documentHelper,
        private readonly SubjectHelperInterface  $subjectHelper,
        private readonly Filesystem              $filesystem,
    ) {
    }

    public function __invoke(DocumentExtraEvent $event): void
    {
        $subject = $event->getSubject();

        if (!$subject instanceof DocumentInterface) {
            return;
        }

        if (null === $sale = $this->documentHelper->getSale($subject)) {
            return;
        }

        $this->filesystemHelper = new FilesystemHelper($this->filesystem);
        $this->event = $event;
        $this->locale = $this->documentHelper->getLocale($subject);

        $this->traverseItems($sale->getItems());

        $this->filesystemHelper = null;
        $this->event = null;
        $this->locale = null;
    }

    /**
     * @param Collection<SaleItemInterface> $items
     *
     * @return void
     */
    private function traverseItems(Collection $items): void
    {
        foreach ($items as $item) {
            if ($item->isPrivate()) {
                continue;
            }

            $this->handleItem($item);

            if (!$item->hasPublicChildren()) {
                continue;
            }

            $this->traverseItems($item->getChildren());
        }
    }

    private function handleItem(SaleItemInterface $item): void
    {
        $subject = $this->subjectHelper->resolve($item);

        if (!$subject instanceof ProductInterface) {
            return;
        }

        $attachments = $subject->getPublicAttachments(ProductAttachmentTypes::TERM_OF_SALE);

        if ($attachments->isEmpty()) {
            return;
        }

        foreach ($attachments as $attachment) {
            if ($this->locale !== $attachment->getLocale()) {
                continue;
            }

            $path = $this->filesystemHelper->getRealPath($attachment->getPath());

            $this->event->addPath($path);
        }
    }
}
