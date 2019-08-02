<?php

namespace Ekyna\Bundle\ProductBundle\Event;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;

/**
 * Class ConvertEvent
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConvertEvent extends ResourceEvent
{
    const FORM_DATA = 'ekyna_product.convert.form_data';
    const PRE_CONVERT = 'ekyna_product.convert.form_data';
    const POST_CONVERT = 'ekyna_product.convert.form_data';

    /**
     * @var string
     */
    private $type;

    /**
     * @var ProductInterface $product
     */
    private $target;


    /**
     * Constructor.
     *
     * @param string           $type
     * @param ProductInterface $source
     * @param ProductInterface $target
     */
    public function __construct(string $type, ProductInterface $source, ProductInterface $target)
    {
        $this->type = $type;
        $this->setResource($source);
        $this->target = $target;
    }

    /**
     * Returns the conversion type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the source.
     *
     * @return ProductInterface
     */
    public function getSource(): ProductInterface
    {
        return $this->getResource();
    }

    /**
     * Returns the target.
     *
     * @return ProductInterface
     */
    public function getTarget(): ProductInterface
    {
        return $this->target;
    }
}
