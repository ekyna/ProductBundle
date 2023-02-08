<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Event\BuildSubjectLabels;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

use function sprintf;

/**
 * Class LabelListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelListener
{
    /**
     * Build subject label event handler.
     *
     * @param BuildSubjectLabels $event
     */
    public function onBuildSubjectLabel(BuildSubjectLabels $event): void
    {
        $labels = $event->getLabels();

        foreach ($labels as $label) {
            $subject = $label->subject;

            if (!$subject instanceof ProductInterface) {
                continue;
            }

            $designation =
                '<strong>' . $subject->getBrand()->getTitle() . '</strong> ' .
                $subject->getFullDesignation();

            $label->designation = $designation;
            $label->reference = $subject->getReference();
            $label->barcode = $subject->getReferenceByType(ProductReferenceTypes::TYPE_EAN_13);
            $label->geocode = $subject->getGeocode();
        }

        $supplierOrder = $event->parameters['supplierOrder'] ?? null;
        if ($supplierOrder instanceof SupplierOrderInterface) {
            $orderedAt = $supplierOrder->getOrderedAt() ?? new DateTime();
            $orderedAt = sprintf('%s (%s)', $supplierOrder->getNumber(), $orderedAt->format('Y-m-d'));

            foreach ($labels as $label) {
                $label['orderedAt'] = $orderedAt;
            }
        }

        if (1 !== count($labels)) {
            return;
        }

        if (!empty($geocode = $event->parameters['geocode'] ?? null)) {
            $labels[0]->geocode = $geocode;
        }
    }
}
