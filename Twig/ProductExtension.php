<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Sale\Product\OptionInterface;

/**
 * Class ProductExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductExtension extends \Twig_Extension
{
    /**
     * Options configuration.
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
    public function initRuntime(\Twig_Environment $twig)
    {
        $this->optionsListTemplate = $twig->loadTemplate('EkynaProductBundle::_options_list.html.twig');
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('options_list', [$this, 'renderOptionsList'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('option_group_label', [$this, 'getOptionGroupLabel'], ['is_safe' => ['html']]),
        ];
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
        $groups = [];

        foreach($this->optionsConfiguration as $groupName => $group) {
            $list = [];
            /** @var \Ekyna\Bundle\ProductBundle\Entity\AbstractOption $option */
            foreach($options as $option) {
                if($option->getGroup() == $groupName) {
                    $list[] = $option;
                }
            }
            if(0 < count($list)) {
                $groups[$group['label']] = $list;
            }
        }

        return $this->optionsListTemplate->render([
        	'options' => $groups
        ]);
    }

    /**
     * Returns the group label for the given option.
     * 
     * @param OptionInterface $option
     * 
     * @return string
     */
    public function getOptionGroupLabel(OptionInterface $option)
    {
        if (array_key_exists($option->getGroup(), $this->optionsConfiguration)) {
            return $this->optionsConfiguration[$option->getGroup()]['label'];
        }
        return '';
    }

    /**
     * {inheritdoc}
     */
    public function getName()
    {
    	return 'ekyna_product';
    }
}
