<?php

namespace Ekyna\Bundle\ProductBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * AbstractsMapsSubscriber.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AbstractsMapsSubscriber implements EventSubscriber
{
    /**
     * The abstract option full qualified class name.
     * 
     * @var string
     */
    const ABSTRACT_OPTION_FQCN  = 'Ekyna\Bundle\CmsBundle\Entity\AbstractOption';

    /**
     * The abstract product full qualified class name.
     * 
     * @var string
     */
    const ABSTRACT_PRODUCT_FQCN = 'Ekyna\Bundle\CmsBundle\Entity\AbstractProduct';

    /**
     * The options discriminitor classes map [name => fqcn].
     * 
     * @var array
     */
    protected $optionsClassesMap;

    /**
     * The products discriminitor classes map [name => fqcn].
     * 
     * @var array
     */
    protected $productsClassesMap;

    /**
     * Constructor.
     * 
     * @param array $optionsClassesMap
     * @param array $productsClassesMap
     */
    public function __construct(array $optionsClassesMap, array $productsClassesMap)
    {
        $this->optionsClassesMap = $optionsClassesMap;
        $this->productsClassesMap = $productsClassesMap;
    }

    /**
     * Sets the discriminator maps on AbstractProduct and AbstractOption entities mappings.
     * 
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        if ($metadata->getName() === self::ABSTRACT_OPTION_FQCN) {
            $namingStrategy = $eventArgs
                ->getEntityManager()
                ->getConfiguration()
                ->getNamingStrategy()
            ;
            $metadata->setDiscriminatorMap($this->optionsClassesMap);

        } elseif ($metadata->getName() === self::ABSTRACT_PRODUCT_FQCN) {
            $namingStrategy = $eventArgs
                ->getEntityManager()
                ->getConfiguration()
                ->getNamingStrategy()
            ;
            $metadata->setDiscriminatorMap($this->productsClassesMap);
        }
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
