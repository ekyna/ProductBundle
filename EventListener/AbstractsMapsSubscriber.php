<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * Class AbstractsMapsSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AbstractsMapsSubscriber implements EventSubscriber
{
    /**
     * The abstract option full qualified class name.
     * 
     * @var string
     */
    const ROOT_OPTION_CLASS  = 'Ekyna\Bundle\CmsBundle\Entity\AbstractOption';

    /**
     * The abstract product full qualified class name.
     * 
     * @var string
     */
    const ROOT_PRODUCT_CLASS = 'Ekyna\Bundle\CmsBundle\Entity\AbstractProduct';

    /**
     * The base option class.
     * 
     * @var string
     */
    protected $baseOptionClass;

    /**
     * The base product class.
     * 
     * @var string
     */
    protected $baseProductClass;

    /**
     * The options discriminator classes map [name => fqcn].
     * 
     * @var array
     */
    protected $optionsClassesMap;

    /**
     * The products discriminator classes map [name => fqcn].
     * 
     * @var array
     */
    protected $productsClassesMap;

    /**
     * Constructor.
     * 
     * @param string $baseOptionClass
     * @param string $baseProductClass
     * @param array  $optionsClassesMap
     * @param array  $productsClassesMap
     */
    public function __construct($baseOptionClass, $baseProductClass, array $optionsClassesMap, array $productsClassesMap)
    {
        $this->baseOptionClass = $baseOptionClass;
        $this->baseProductClass = $baseProductClass;
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

        /** @var \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata */
        $metadata = $eventArgs->getClassMetadata();

        // Option mapping
        if ($metadata->getName() === $this->baseOptionClass) {
            $metadata->setDiscriminatorMap($this->optionsClassesMap);
        // Product mapping
        } elseif ($metadata->getName() === $this->baseProductClass) {
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
