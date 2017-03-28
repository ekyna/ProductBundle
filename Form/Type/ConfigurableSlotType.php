<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Form\DataTransformer\ProductToBundleSlotChoiceTransformer;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface;
use Liip\ImagineBundle\Imagine\Cache as Imagine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfigurableSlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @todo    CommerceBundle DI pass
 */
class ConfigurableSlotType extends AbstractType implements Imagine\CacheManagerAwareInterface
{
    use Imagine\CacheManagerAwareTrait;

    /**
     * @var ProductProvider
     */
    private $productProvider;

    /**
     * @var string
     */
    private $noImagePath;


    /**
     * Constructor.
     *
     * @param ProductProvider $productProvider
     * @param string          $noImagePath
     */
    public function __construct(ProductProvider $productProvider, $noImagePath)
    {
        $this->productProvider = $productProvider;
        $this->noImagePath = $noImagePath;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var BundleSlotInterface $bundleSlot */
        $bundleSlot = $options['bundle_slot'];

        // TODO subject property does no longer exist on sale item.
        // -> mapped = false
        // -> 'post_submit' event to re assign (need higher priority than sale item 'build' event)
        $subjectField = $builder
            ->create('subject', Type\ChoiceType::class, [
                'property_path' => 'subjectIdentity.subject',
                'label'         => $bundleSlot->getDescription(),
                'choices'       => $bundleSlot->getChoices(),
                'choice_value'  => 'id',
                'choice_label'  => 'product.designation',
                'choice_attr'   => function (BundleChoiceInterface $choice) {
                    return [
                        'data-config' => json_encode($this->buildChoiceAttributes($choice)),
                    ];
                },
                'expanded'      => true,
            ])
            ->addModelTransformer(new ProductToBundleSlotChoiceTransformer($bundleSlot));

        $builder
            ->add($subjectField)
            ->add('quantity', Type\IntegerType::class, [
                'label' => 'ekyna_core.field.quantity',
                'attr'  => [
                    'min' => 1,
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                /** @var SaleItemInterface $item */
                $item  = $event->getData();

                $product = $item->getSubjectIdentity()->getSubject();
                $item->getSubjectIdentity()->clear();

                $this->productProvider->assign($item, $product);

                $event->setData($item);
            }, 2048);
    }

    /**
     * @inheritDoc
     */
    public function buildChoiceAttributes(BundleChoiceInterface $choice)
    {
        $product = $choice->getProduct();

        $attributes = [
            'min_quantity' => $choice->getMinQuantity(),
            'max_quantity' => $choice->getMaxQuantity(),
            'title'        => $product->getTitle(),
            'description'  => $product->getDescription(),
            'image'        => $this->noImagePath,
            'price'        => $product->getNetPrice(), // TODO
        ];

        $images = $choice->getProduct()->getMedias([MediaTypes::IMAGE]);
        if (0 < $images->count()) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface $image */
            $image = $images->first();
            $attributes['image'] = $this
                ->cacheManager
                ->getBrowserPath($image->getMedia()->getPath(), 'configurable_slot');
        }

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var BundleSlotInterface $bundleSlot */
        $bundleSlot = $options['bundle_slot'];

        $view->vars['slot_title'] = $bundleSlot->getTitle();
        $view->vars['slot_description'] = $bundleSlot->getDescription();
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('label', false)
            ->setDefault('data_class', SaleItemInterface::class)
            ->setRequired(['bundle_slot'])
            ->setAllowedTypes('bundle_slot', BundleSlotInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_configurable_slot';
    }
}
