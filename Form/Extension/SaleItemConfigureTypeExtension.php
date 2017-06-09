<?php

namespace Ekyna\Bundle\ProductBundle\Form\Extension;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\FormHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SaleItemConfigureTypeExtension
 * @package Ekyna\Bundle\ProductBundle\Form\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemConfigureTypeExtension extends AbstractTypeExtension
{
    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $theme;


    /**
     * Constructor.
     *
     * @param FormHelper        $formHelper
     * @param \Twig_Environment $twig
     * @param string            $theme
     */
    public function __construct(
        FormHelper $formHelper,
        \Twig_Environment $twig,
        $theme = 'EkynaProductBundle:Form:sale_item_configure.html.twig'
    ) {
        $this->formHelper = $formHelper;
        $this->twig = $twig;
        $this->theme = $theme;
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

        // Set the form theme
        /** @var \Symfony\Bridge\Twig\Extension\FormExtension $extension */
        $extension = $this->twig->getExtension('form');
        $extension->renderer->setTheme($view, $this->theme);

        $config = $this->formHelper->getPricingConfig($item, !$options['admin_mode']);

        $view->vars['pricing'] = $config;
        $view->vars['attr']['data-config'] = json_encode($config);
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return SaleItemConfigureType::class;
    }
}
