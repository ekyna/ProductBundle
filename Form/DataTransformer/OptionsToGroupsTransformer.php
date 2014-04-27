<?php

namespace Ekyna\Bundle\ProductBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * OptionsToGroupsTransformer
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OptionsToGroupsTransformer implements DataTransformerInterface
{
    /**
     * @var EntityRepository
     */
    private $optionGroupRepository;

    /**
     * @param EntityRepository $optionGroupRepository
     */
    public function __construct(EntityRepository $optionGroupRepository)
    {
        $this->optionGroupRepository = $optionGroupRepository;
    }

    /**
     * Transforms a Option collection to an associativ array : OptionGroup Id => Option collection
     * 
     * @param \Doctrine\Common\Collections\ArrayCollection $sellableOptions
     * 
     * @return array
     */
    public function transform($options)
    {
        $array = array();

    	$optionGroups = $this->optionGroupRepository->findBy(array(), array('position' => 'ASC'));
    	foreach($optionGroups as $optionGroup) {
    	    foreach($options as $option) {
    	        if($option->getGroup() == $optionGroup) {
    	            $array[$optionGroup->getId()][] = $option;
    	        }
    	    }
    	}

    	return $array;
    }

    /**
     * Transforms an associativ array to an Option collection
     * 
     * @param array $array
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function reverseTransform($array)
    {
        $collection = new ArrayCollection();

        foreach($array as $groupId => $options) {
            if(null === $optionGroup = $this->optionGroupRepository->find($groupId)) {
                throw new \RuntimeException('Unable to reverse transform options.');
            }
            foreach($options as $option) {
                // Set option (group)
                $option->setGroup($optionGroup);
                $collection->add($option);
            }
        }

        return $collection;
    }
}
