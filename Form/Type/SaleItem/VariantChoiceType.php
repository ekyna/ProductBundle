<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class VariantChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantChoiceType extends AbstractType
{

    /**
     * @var ProductProvider
     */
    private $provider;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;


    /**
     * Constructor.
     *
     * @param                         $provider
     * @param LocaleProviderInterface $localeProvider
     */
    public function __construct($provider, LocaleProviderInterface $localeProvider)
    {
        $this->provider = $provider;
        $this->localeProvider = $localeProvider;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Model\ProductInterface $variable */
        $variable = $options['variable'];

        $builder->addModelTransformer(new IdToChoiceObjectTransformer($variable->getVariants()->toArray()));

        // TODO POST_SUBMIT => Build item from variant
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $formatter = \NumberFormatter::create($this->localeProvider->getCurrentLocale(), \NumberFormatter::CURRENCY);

        $resolver
            ->setDefaults([
                'label'         => 'ekyna_product.variant.label.singular',
                //'data_class'    => SaleItemInterface::class,
                'property_path' => 'data[' . ItemBuilder::VARIANT_ID . ']',
                'select2'       => false,
                'choices'       => function (Options $options, $value) {
                    /** @var Model\ProductInterface $variable */
                    $variable = $options['variable'];

                    return $variable->getVariants();
                },
                'choice_value'  => function (Model\ProductInterface $variant) {
                    return $variant->getId();
                },
                'choice_label'  => function (Model\ProductInterface $variant) use ($formatter) {
                    $title = $variant->getTitle();
                    if (0 < $netPrice = $variant->getNetPrice()) {
                        // TODO User currency
                        $title .= sprintf(' (%s)', $formatter->formatCurrency($variant->getNetPrice(), 'EUR'));
                    }
                    return $title;
                },
                'choice_attr'   => function (Model\ProductInterface $variant) {
                    return [
                        'data-config' => json_encode([
                            'net_price' => $variant->getNetPrice(),
                            // TODO options config
                        ]),
                    ];
                },
                'constraints'   => [
                    new NotNull(),
                ],
            ])
            ->setRequired(['variable'])
            ->setAllowedTypes('variable', Model\ProductInterface::class)
            ->setAllowedValues('variable', function (Model\ProductInterface $variable) {
                return $variable->getType() === Model\ProductTypes::TYPE_VARIABLE;
            });
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
