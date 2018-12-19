<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template\SlotsType;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template\TemplateChoiceType;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CatalogPageType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogPageType extends AbstractType
{
    /**
     * @var CatalogRegistry
     */
    protected $registry;


    /**
     * Constructor.
     *
     * @param CatalogRegistry $registry
     */
    public function __construct(CatalogRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('template', TemplateChoiceType::class, [
                'attr'    => [
                    'class' => 'catalog-page-template',
                ],
            ])
            ->add('number', CollectionPositionType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var CatalogPage $data */
                $data = $event->getData();

                $this->buildSlotsForm($event->getForm(), $data ? $data->getTemplate() : null);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                $this->buildSlotsForm($event->getForm(), $data['template']);
            });
    }

    /**
     * Build the slots form.
     *
     * @param FormInterface $form
     * @param null          $templateName
     */
    public function buildSlotsForm(FormInterface $form, $templateName = null)
    {
        $type = empty($templateName) ? SlotsType::class : $this->registry->getTemplate($templateName)['form_type'];

        $form->add('slots', $type);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'catalog-page');

        $view->vars['attr']['name'] = $view->vars['full_name'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CatalogPage::class,
        ]);
    }
}
