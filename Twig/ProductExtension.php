<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Doctrine\Common\Collections\Collection;

/**
 * ProductExtension.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductExtension extends \Twig_Extension
{
    /**
     * The options configuration.
     * 
     * @var array
     */
    protected $optionsConfiguration;

    /**
     * Options list template.
     *
     * @var \Twig_Template
     */
    protected $optionsListTemplate;

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
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->optionsListTemplate = $environment->loadTemplate('EkynaProductBundle::_options_list.html.twig');
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
     * Renders a list of product options.
     * 
     * @param Collection $options
     * 
     * @return string
     */
    public function renderOptionsList(Collection $options)
    {
        $groups = array();

        foreach($this->optionsConfiguration as $groupName => $group) {
            $list = array();
            foreach($options as $option) {
                if($option->getGroup() == $groupName) {
                    $list[] = $option;
                }
            }
            if(0 < count($list)) {
                $groups[$group['label']] = $list;
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
