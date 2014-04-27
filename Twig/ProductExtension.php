<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

/**
 * ProductExtension
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductExtension extends \Twig_Extension
{
    /**
     * OptionGroup repository
     * 
     * @var EntityRepository
     */
    protected $optionGroupRepository;

    /**
     * Options list template
     *
     * @var \Twig_Template
     */
    protected $optionsListTemplate;

    /**
     * Constructor
     * 
     * @param EntityRepository $optionGroupRepository
     */
    public function __construct(EntityRepository $optionGroupRepository)
    {
        $this->optionGroupRepository = $optionGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->optionsListTemplate = $environment->loadTemplate('EkynaProductBundle:OptionGroup:_options_list.html.twig');
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('options_list', array($this, 'renderOptionsList'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders a list of product options
     * 
     * @param Collection $options
     * 
     * @return string
     */
    public function renderOptionsList(Collection $options)
    {
        $groups = array();

        $optionGroups = $this->optionGroupRepository->findAll();
        foreach($optionGroups as $optionGroup) {
            foreach($options as $option) {
                if($option->getGroup() == $optionGroup) {
                    $optionGroup->addOption($option);
                }
            }
            if($optionGroup->hasOptions()) {
                $groups[] = $optionGroup;
            }
        }

        return $this->optionsListTemplate->render(array(
        	'options' => $groups
        ));
    }

    /**
     * {inheritdoc}
     */
    public function getName()
    {
    	return 'ekyna_product';
    }
}
