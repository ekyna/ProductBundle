<?php

namespace Ekyna\Bundle\ProductBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * ProductsMapSubscriber.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductsMapSubscriber implements EventSubscriber
{
    /**
     * The CTI parent full qualified class name.
     * 
     * @var string
     */
    const PARENT_FQCN = 'Ekyna\Bundle\CmsBundle\Entity\AbstractProduct';

    /**
     * The discriminitor map [name => fqcn].
     * 
     * @var array
     */
    protected $discriminatorMap;

    /**
     * Constructor.
     * 
     * @param array $discriminatorMap
     */
    public function __construct(array $discriminatorMap)
    {
        $this->discriminatorMap = $discriminatorMap;
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        if ($metadata->getName() !== self::PARENT_FQCN) {
            return;
        }

        $namingStrategy = $eventArgs
            ->getEntityManager()
            ->getConfiguration()
            ->getNamingStrategy()
        ;

        $metadata->setDiscriminatorMap($this->discriminatorMap);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
        );
    }
}
