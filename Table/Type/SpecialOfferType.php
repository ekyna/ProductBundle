<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SpecialOfferType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferType extends ResourceTableType
{
    /**
     * @var string
     */
    private $brandClass;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $brandClass
     */
    public function __construct(string $dataClass, string $brandClass)
    {
        parent::__construct($dataClass);

        $this->brandClass = $brandClass;
    }

    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
        }

        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_product_special_offer_admin_show',
                'route_parameters_map' => [
                    'specialOfferId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('percent', CType\Column\NumberType::class, [
                'label'    => 'ekyna_product.common.percent',
                'append'   => '%',
                'position' => 20,
            ])
            ->addColumn('startsAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.start_date',
                'time_format' => 'none',
                'position'    => 40,
            ])
            ->addColumn('endsAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.end_date',
                'time_format' => 'none',
                'position'    => 50,
            ]);

        if (null !== $product = $options['product']) {
            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($product) {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
                    ->setParameter('product', $product);
            });

            $builder
                ->setFilterable(false)
                ->setSortable(false)
                ->setPerPageChoices([100])
                ->addColumn('enabled', CType\Column\BooleanType::class, [
                    'label'    => 'ekyna_core.field.enabled',
                    'position' => 60,
                ]);

            return;
        } else {
            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($product) {
                $qb->andWhere($qb->expr()->isNull($alias . '.product'));
            });
        }

        $builder
            ->addColumn('brands', DType\Column\EntityType::class, [
                'label'                => 'ekyna_product.brand.label.plural',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_product_brand_admin_show',
                'route_parameters_map' => ['brandId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.enabled',
                'route_name'           => 'ekyna_product_special_offer_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['specialOfferId' => 'id'],
                'position'             => 60,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_special_offer_admin_edit',
                        'route_parameters_map' => ['specialOfferId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_special_offer_admin_remove',
                        'route_parameters_map' => ['specialOfferId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ])
            ->addFilter('percent', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_product.common.percent',
                'position' => 20,
            ])
            ->addFilter('brands', DType\Filter\EntityType::class, [
                'label'        => 'ekyna_product.brand.label.singular',
                'class'        => $this->brandClass,
                'entity_label' => 'name',
                'position'     => 30,
            ])
            ->addFilter('startsAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.start_date',
                'position' => 40,
            ])
            ->addFilter('endsAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.end_date',
                'position' => 50,
            ])
            ->addFilter('enabled', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'position' => 60,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('product', null)
            ->setAllowedTypes('product', [ProductInterface::class, 'null']);
    }
}
