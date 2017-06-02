<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
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
     * Constructor.
     *
     * @param ResourceRepositoryInterface $optionRepository
     * @param ProductProvider             $provider
     */
    public function __construct(ResourceRepositoryInterface $optionRepository, ProductProvider $provider)
    {
        $this->optionRepository = $optionRepository;
        $this->provider = $provider;
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

        $choices = $optionGroup->getOptions();

        $options = $builder
            ->create('choice', ChoiceType::class, [
                'label'         => false,
                'property_path' => 'data[' . ItemBuilder::OPTION_ID . ']',
                'choices'       => $optionGroup->getOptions(),
                'choice_label' => function(Model\OptionInterface $option) {
                    return $option->getTitle();
                },
                'choice_attr' => function(Model\OptionInterface $option) {
                    return [
                        'data-price' => $option->getNetPrice(),
                    ];
                },
                'attr' => [
                    'class' => 'sale-item-option',
                ],
                'constraints'   => $constraints,
                'required'      => $required,
                'select2'       => false,
            ])
            ->addModelTransformer(new Form\CallbackTransformer(
                // id to option
                function($value) use ($choices) {
                    /** @var Model\OptionInterface $option */
                    foreach ($choices as $option) {
                        if ($option->getId() == $value) {
                            return $option;
                        }
                    }

                    return $value;
                },
                // option to id
                function($value) use ($choices) {
                    if ($value instanceof Model\OptionInterface) {
                        return $value->getId();
                    }

                    return $value;
                }
            ))
            ->addEventListener(Form\FormEvents::POST_SUBMIT, function (Form\FormEvent $event) use ($optionGroup) {
                /** @var SaleItemInterface $data */
                $item = $event->getForm()->getParent()->getData();

                $option = null;
                $optionId = $event->getData();
                if (0 < $optionId) {
                    /** @var Model\OptionInterface $option */
                    if (null !== $option = $this->optionRepository->find($optionId)) {
                        $this
                            ->provider
                            ->getItemBuilder()
                            ->buildItemFromOption($item, $option);
                    }
                }

                if (null === $option && !$optionGroup->isRequired()) {
                    // Prevent validation (item will be removed)
                    $event->stopPropagation();
                }
            }, 1024);

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
