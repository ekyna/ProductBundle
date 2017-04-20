<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Exception\UnexpectedTypeException;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;

/**
 * Class AttributeConfigType
 * @package Ekyna\Bundle\ProductBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeConfigType extends AbstractType
{
    private AttributeTypeRegistryInterface $registry;


    /**
     * Constructor.
     *
     * @param AttributeTypeRegistryInterface $typeRegistry
     */
    public function __construct(AttributeTypeRegistryInterface $typeRegistry)
    {
        $this->registry = $typeRegistry;
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        if (!$value instanceof AttributeInterface) {
            throw new UnexpectedTypeException($value, AttributeInterface::class);
        }

        $type = $this->registry->getType($value->getType());

        $fields = $type->getConfigShowFields($value);

        $view->vars['value'] = $fields;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'product_attribute_config';
    }
}
