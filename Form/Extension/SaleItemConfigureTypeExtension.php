<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Extension;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
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
    private FormBuilder           $formBuilder;
    private FormRendererInterface $formRenderer;
    private string                $theme;
    private bool                  $displayOffers = false;

    public function __construct(
        FormBuilder           $formBuilder,
        FormRendererInterface $formRenderer,
        string                $theme = '@EkynaProduct/Form/sale_item_configure.html.twig'
    ) {
        $this->formBuilder = $formBuilder;
        $this->formRenderer = $formRenderer;
        $this->theme = $theme;
    }

    /**
     * Sets whether to display offers.
     */
    public function setDisplayOffers(bool $display): void
    {
        $this->displayOffers = $display;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var SaleItemInterface $item */
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
     */
    private function doDisplayOffers(FormView $view): bool
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
                /** @var ChoiceView $choice */
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
                        /** @var ChoiceView $choice */
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

    public static function getExtendedTypes(): array
    {
        return [SaleItemConfigureType::class];
    }
}
