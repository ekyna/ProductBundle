<?php

namespace Ekyna\Bundle\ProductBundle\Form\Extension;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SaleItemConfigureTypeExtension
 * @package Ekyna\Bundle\ProductBundle\Form\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemConfigureTypeExtension extends AbstractTypeExtension
{
    /**
     * @var FormBuilder
     */
    private $formBuilder;

    /**
     * @var FormRendererInterface
     */
    private $formRenderer;

    /**
     * @var string
     */
    private $theme;

    /**
     * @var bool
     */
    private $displayOffers = false;


    /**
     * Constructor.
     *
     * @param FormBuilder           $formBuilder
     * @param FormRendererInterface $formRenderer
     * @param string                $theme
     */
    public function __construct(
        FormBuilder $formBuilder,
        FormRendererInterface $formRenderer,
        $theme = '@EkynaProduct/Form/sale_item_configure.html.twig'
    ) {
        $this->formBuilder = $formBuilder;
        $this->formRenderer = $formRenderer;
        $this->theme = $theme;
    }

    /**
     * Sets whether to display offers.
     *
     * @param bool $display
     */
    public function setDisplayOffers(bool $display)
    {
        $this->displayOffers = $display;
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        if (null === $item = $form->getData()) {
            return;
        }

        // Abort if subject is not an instance of ProductInterface
        $subject = $item->getSubjectIdentity()->getSubject();
        if (!$subject instanceof ProductInterface) {
            return;
        }

        $view->vars['subject'] = $subject;

        // Set the form theme
        $this->formRenderer->setTheme($view, $this->theme);

        $config = $this->formBuilder->getFormConfig($item);
        $config['privileged'] = $options['admin_mode'];

        $view->vars['attr']['data-config'] = $config;
        $view->vars['attr']['data-globals'] = $this->formBuilder->getFormGlobals();
        $view->vars['attr']['data-trans'] = $this->formBuilder->getFormTranslations();

        $view->vars['has_offers'] = $this->doDisplayOffers($view);
    }

    /**
     * Returns whether offers should be displayed if any.
     *
     * @param FormView $view
     *
     * @return bool
     */
    private function doDisplayOffers(FormView $view)
    {
        if (!$this->displayOffers) {
            return false;
        }

        if (isset($view->vars['attr']['data-config']['pricing'])) {
            if (1 < count($view->vars['attr']['data-config']['pricing']['offers'])) {
                return true;
            }
        }

        foreach ($view->children as $name => $child) {
            if ($name === 'configure') {
                return false;
            }

            if ($name === 'variant') {
                /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
                foreach ($child->vars['choices'] as $choice) {
                    if (1 < count($choice->attr['data-config']['pricing']['offers'])) {
                        return true;
                    }
                }
            }

            if ($name === 'options') {
                /** @var FormView $optionGroup */
                foreach ($child->children as $optionGroup) {
                    foreach ($optionGroup->children as $option) {
                        /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
                        foreach ($option->vars['choices'] as $choice) {
                            if (1 < count($choice->attr['data-config']['pricing']['offers'])) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return SaleItemConfigureType::class;
    }
}
