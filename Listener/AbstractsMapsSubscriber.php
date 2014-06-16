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
     * @param string $optionClass
     * @param string $productClass
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
