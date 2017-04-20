<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SpecialOfferType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new UnexpectedTypeException($source, EntitySource::class);
        }

        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('percent', CType\Column\NumberType::class, [
                'label'    => t('common.percent', [], 'EkynaProduct'),
                'append'   => '%',
                'position' => 20,
            ])
            ->addColumn('startsAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.start_date', [], 'EkynaUi'),
                'time_format' => 'none',
                'position'    => 40,
            ])
            ->addColumn('endsAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.end_date', [], 'EkynaUi'),
                'time_format' => 'none',
                'position'    => 50,
            ]);

        if (null !== $product = $options['product']) {
            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($product): void {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
                    ->setParameter('product', $product);
            });

            $builder
                ->setFilterable(false)
                ->setSortable(false)
                ->setPerPageChoices([100])
                ->addColumn('enabled', CType\Column\BooleanType::class, [
                    'label'    => t('field.enabled', [], 'EkynaUi'),
                    'position' => 60,
                ]);

            return;
        } else {
            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($product): void {
                $qb->andWhere($qb->expr()->isNull($alias . '.product'));
            });
        }

        $builder
            ->addColumn('brands', DType\Column\EntityType::class, [
                'label'        => t('brand.label.plural', [], 'EkynaProduct'),
                'entity_label' => 'name',
                'position'     => 30,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'property' => 'enabled',
                'position' => 60,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('percent', CType\Filter\NumberType::class, [
                'label'    => t('common.percent', [], 'EkynaProduct'),
                'position' => 20,
            ])
            ->addFilter('brands', ResourceType::class, [
                'resource' => 'ekyna_product.brand',
                'position' => 30,
            ])
            ->addFilter('startsAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.start_date', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addFilter('endsAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.end_date', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addFilter('enabled', CType\Filter\BooleanType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'position' => 60,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('product', null)
            ->setAllowedTypes('product', [ProductInterface::class, 'null']);
    }
}
