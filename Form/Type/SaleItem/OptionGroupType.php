<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\DataTransformer\IdToChoiceObjectTransformer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class OptionGroupType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupType extends Form\AbstractType
{
    /**
     * @var ResourceRepositoryInterface
     */
    private $optionRepository;

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
     * @param ResourceRepositoryInterface $optionRepository
     * @param ProductProvider             $provider
     * @param LocaleProviderInterface     $localeProvider
     */
    public function __construct(
        ResourceRepositoryInterface $optionRepository,
        ProductProvider $provider,
        LocaleProviderInterface $localeProvider
    ) {
        $this->optionRepository = $optionRepository;
        $this->provider = $provider;
        $this->localeProvider = $localeProvider;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        /** @var Model\OptionGroupInterface $optionGroup */
        $optionGroup = $options['option_group'];

        $required = false;
        $constraints = [];
        if ($optionGroup->isRequired()) {
            $constraints[] = new NotNull();
            $required = true;
        }

        $choices = $optionGroup->getOptions()->toArray();

        $transformer = new IdToChoiceObjectTransformer($choices);
        $formatter = \NumberFormatter::create($this->localeProvider->getCurrentLocale(), \NumberFormatter::CURRENCY);

        $choiceValue = function (Model\OptionInterface $option = null) {
            if (null !== $option) {
                return $option->getId();
            }
            return null;
        };

        $choiceLabel = function (Model\OptionInterface $option = null) use ($formatter) {
            if (null !== $option) {
                if (0 < $netPrice = $option->getNetPrice()) {
                    // TODO User currency
                    return sprintf('%s (%s)', $option->getTitle(), $formatter->formatCurrency($option->getNetPrice(), 'EUR'));
                }

                return $option->getTitle();
            }

            return null;
        };

        $choiceAttributes = function (Model\OptionInterface $option = null) {
            if (null !== $option) {
                return [
                    'data-price' => $option->getNetPrice(),
                ];
            }
            return [];
        };

        $postSubmitListener = function (Form\FormEvent $event) use ($optionGroup, $transformer) {
            /** @var SaleItemInterface $data */
            $item = $event->getForm()->getParent()->getData();
            $data = $event->getData();

            if (null !== $option = $transformer->reverseTransform($data)) {
                /** @var Model\OptionInterface $option */
                $this
                    ->provider
                    ->getItemBuilder()
                    ->buildItemFromOption($item, $option);
            } elseif (!$optionGroup->isRequired()) {
                // Prevent validation (item will be removed)
                $event->stopPropagation();
            }
        };

        $options = $builder
            ->create('choice', ChoiceType::class, [
                'label'         => false,
                'property_path' => 'data[' . ItemBuilder::OPTION_ID . ']',
                'choices'       => $choices,
                'choice_value'  => $choiceValue,
                'choice_label'  => $choiceLabel,
                'choice_attr'   => $choiceAttributes,
                'attr'          => [
                    'class' => 'sale-item-option',
                ],
                'constraints'   => $constraints,
                'placeholder'   => 'ekyna_product.sale_item_configure.choose_option',
                'required'      => $required,
                'select2'       => false,
            ])
            ->addModelTransformer($transformer)
            ->addEventListener(Form\FormEvents::POST_SUBMIT, $postSubmitListener, 1024);

        $builder->add($options);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SaleItemInterface::class,
            ])
            ->setRequired(['option_group'])
            ->setAllowedTypes('option_group', Model\OptionGroupInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_sale_item_option_group';
    }
}
