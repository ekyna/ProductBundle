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
                'attr' => [
                    'class' => 'catalog-page-template',
                ],
            ])
            ->add('number', CollectionPositionType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                /** @var CatalogPage $data */
                $data = $event->getData();
                $template = $data ? $data->getTemplate() : null;

                $this->buildSlotsForm($form, $template);
                $this->buildOptionsForm($form, $template);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $this->buildSlotsForm($form, $data['template']);
                $this->buildOptionsForm($form, $data['template']);
            });
    }

    /**
     * Build the data form.
     *
     * @param FormInterface $form
     * @param string|null   $template
     */
    public function buildOptionsForm(FormInterface $form, string $template = null)
    {
        if (empty($template)) {
            return;
        }

        if (empty($type = $this->registry->getTemplate($template)['form_type'])) {
            return;
        }

        $form->add('options', $type);
    }

    /**
     * Build the slots form.
     *
     * @param FormInterface $form
     * @param string|null   $template
     */
    public function buildSlotsForm(FormInterface $form, string $template = null)
    {
        $count = empty($template) ? 0 : (int)$this->registry->getTemplate($template)['slots'];

        if (0 >= $count) {
            return;
        }

        $form->add('slots', SlotsType::class, [
            'slot_count' => $count,
        ]);
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

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_catalog_page';
    }
}
