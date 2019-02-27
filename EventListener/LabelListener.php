<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SubjectLabelEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LabelListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelListener implements EventSubscriberInterface
{
    /**
     * Build subject label event handler.
     *
     * @param SubjectLabelEvent $event
     */
    public function onBuildSubjectLabel(SubjectLabelEvent $event)
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

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SubjectLabelEvent::BUILD => ['onBuildSubjectLabel', 0],
        ];
    }
}
