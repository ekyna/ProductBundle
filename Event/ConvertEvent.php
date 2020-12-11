<?php

namespace Ekyna\Bundle\ProductBundle\Event;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class ConvertEvent
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConvertEvent extends ResourceEvent
{
    public const FORM_DATA    = 'ekyna_product.convert.form_data';
    public const PRE_CONVERT  = 'ekyna_product.convert.pre_convert';
    public const POST_CONVERT = 'ekyna_product.convert.post_convert';
    public const DONE_CONVERT = 'ekyna_product.convert.done_convert';

    /**
     * @var ProductInterface
     */
    private $target;

    /**
     * @var FormInterface|null
     */
    private $form;

    /**
     * @var bool
     */
    private $success;


    /**
     * Constructor.
     *
     * @param ProductInterface $source
     * @param ProductInterface           $target
     */
    public function __construct(ProductInterface $source, ProductInterface $target)
    {
        $this->setResource($source);
        $this->target = $target;
        $this->success = false;
    }

    /**
     * @inheritDoc
     */
    public function setResource(ResourceInterface $resource)
    {
        if (!$resource instanceof ProductInterface) {
            throw new UnexpectedTypeException($resource, ProductInterface::class);
        }

        parent::setResource($resource);
    }

    /**
     * Returns the source.
     *
     * @return ProductInterface
     */
    public function getSource(): ProductInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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

    /**
     * Returns the source type.
     *
     * @return string
     */
    public function getSourceType(): string
    {
        return $this->getSource()->getType();
    }

    /**
     * Returns the target type.
     *
     * @return string
     */
    public function getTargetType(): string
    {
        return $this->target->getType();
    }

    /**
     * Returns whether this event is of the given types.
     *
     * @param string $source
     * @param string $target
     *
     * @return bool
     */
    public function isType(string $source, string $target): bool
    {
        return $source === $this->getSourceType()
            && $target === $this->getTargetType();
    }

    /**
     * Returns the form.
     *
     * @return FormInterface|null
     */
    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    /**
     * Sets the form.
     *
     * @param FormInterface|null $form
     */
    public function setForm(FormInterface $form = null): void
    {
        $this->form = $form;
    }

    /**
     * Returns the success.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Sets the success.
     *
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }
}
