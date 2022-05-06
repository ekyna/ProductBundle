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
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class PricingType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PricingType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new UnexpectedTypeException($source, EntitySource::class);
        }

        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'         => t('field.name', [], 'EkynaUi'),
                'property_path' => false,
                'position'      => 10,
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
            ]);

        if (null !== $product = $options['product']) {
            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($product): void {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
                    ->setParameter('product', $product);
            });

            return;
        } else {
            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($product): void {
                $qb->andWhere($qb->expr()->isNull($alias . '.product'));
            });
        }

        $builder
            ->addFilter('brands', ResourceType::class, [
                'resource' => 'ekyna_product.brand',
                'position' => 10,
            ])
            ->addFilter('groups', ResourceType::class, [
                'resource' => 'ekyna_commerce.customer_group',
                'position' => 20,
            ])
            ->addFilter('countries', ResourceType::class, [
                'resource' => 'ekyna_commerce.country',
                'position' => 30,
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
