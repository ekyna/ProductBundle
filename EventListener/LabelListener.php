<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SubjectLabelEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;

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
     * @param SubjectLabelEvent $event
     */
    public function onBuildSubjectLabel(SubjectLabelEvent $event): void
    {
        $labels = $event->getLabels();

        foreach ($labels as $label) {
            $subject = $label->getSubject();

            if (!$subject instanceof ProductInterface) {
                continue;
            }

            $designation =
                '<strong>' . $subject->getBrand()->getTitle() . '</strong> ' .
                $subject->getFullDesignation();

            $label
                ->setDesignation($designation)
                ->setReference($subject->getReference())
                ->setBarcode($subject->getReferenceByType(ProductReferenceTypes::TYPE_EAN_13))
                ->setGeocode($subject->getGeocode());
        }
    }
}
