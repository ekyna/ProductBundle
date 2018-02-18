<?php

namespace Ekyna\Bundle\ProductBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
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
    /**
     * @var AttributeTypeRegistryInterface
     */
    private $typeRegistry;


    /**
     * Constructor.
     *
     * @param AttributeTypeRegistryInterface $typeRegistry
     */
    public function __construct(AttributeTypeRegistryInterface $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        if (!$value instanceof AttributeInterface) {
            throw new InvalidArgumentException("Expected instance of " . AttributeInterface::class);
        }

        $type = $this->typeRegistry->getType($value->getType());

        $fields = $type->getConfigShowFields($value);

        $view->vars['value'] = $fields;
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'attribute_config';
    }
}
