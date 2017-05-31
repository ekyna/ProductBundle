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

        $choices = [];
        foreach ($optionGroup->getOptions() as $option) {
            $choices[$option->getTitle()] = $option->getId();
        }

        $required = false;
        $constraints = [];
        if ($optionGroup->isRequired()) {
            $constraints[] = new NotNull();
            $required = true;
        }

        $options = $builder
            ->create('choice', ChoiceType::class, [
                'label'         => false,
                'property_path' => 'data[' . ItemBuilder::OPTION_ID . ']',
                'choices'       => $choices,
                'constraints'   => $constraints,
                'required'      => $required,
                'select2'       => false,
            ])
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
                'data_class' => SaleItemInterface::class
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
