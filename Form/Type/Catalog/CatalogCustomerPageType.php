<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog;

use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template\SlotsType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CatalogCustomerPageType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogCustomerPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', CollectionPositionType::class)
            ->add('slots', SlotsType::class, [
                'label'      => false,
                'slot_count' => 2,
                'attr'       => [
                    'label_col'  => 0,
                    'widget_col' => 12,
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var CatalogPage $data */
                if (null === $data = $event->getData()) {
                    return;
                }
                $data->setTemplate('default.half');
            }, 2048)
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                /** @var CatalogPage $data */
                $data = $event->getData();
                $data->setTemplate('default.half');
            }, 2048);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CatalogPage::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_catalog_page';
    }
}
