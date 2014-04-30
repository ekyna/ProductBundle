<?php

namespace Ekyna\Bundle\ProductBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * OptionsToGroupsTransformer.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OptionsToGroupsTransformer implements DataTransformerInterface
{
    /**
     * The options configuration.
     * 
     * @var array
     */
    protected $optionsConfiguration;

    /**
     * Constructor.
     * 
     * @param array $optionsConfiguration
     */
    public function __construct(array $optionsConfiguration)
    {
        $this->optionsConfiguration = $optionsConfiguration;
    }

    /**
     * Transforms a Option collection to an associativ array : group => Option collection.
     * 
     * @param \Doctrine\Common\Collections\ArrayCollection $options
     * 
     * @return array
     */
    public function transform($options)
    {
        $array = array();

    	foreach ($this->optionsConfiguration as $groupName => $group) {
    	    foreach ($options as $option) {
    	        if ($option->getGroup() == $groupName) {
    	            $array[$groupName][] = $option;
    	        }
    	    }
    	}

    	return $array;
    }

    /**
     * Transforms an associativ array to an Option collection.
     * 
     * @param array $array
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function reverseTransform($array)
    {
        $collection = new ArrayCollection();

        foreach($array as $groupName => $options) {
            if (! array_key_exists($groupName, $this->optionsConfiguration)) {
                throw new \RuntimeException('Unable to reverse transform options.');
            }
            foreach ($options as $option) {
                // Set option (group)
                $option->setGroup($groupName);
                $collection->add($option);
            }
        }

        return $collection;
    }
}
