<?php

declare(strict_types=1);

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
final class ConvertEvent extends ResourceEvent
{
    public const FORM_DATA    = 'ekyna_product.convert.form_data';
    public const PRE_CONVERT  = 'ekyna_product.convert.pre_convert';
    public const POST_CONVERT = 'ekyna_product.convert.post_convert';
    public const DONE_CONVERT = 'ekyna_product.convert.done_convert';

    private ProductInterface $target;
    private ?FormInterface $form = null;
    private bool $success = false;

    public function __construct(ProductInterface $source, ProductInterface $target)
    {
        $this->setResource($source);
        $this->target = $target;
    }

    public function setResource(ResourceInterface $resource): void
    {
        if (!$resource instanceof ProductInterface) {
            throw new UnexpectedTypeException($resource, ProductInterface::class);
        }

        parent::setResource($resource);
    }

    public function getSource(): ProductInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getResource();
    }

    public function getTarget(): ProductInterface
    {
        return $this->target;
    }

    public function getSourceType(): string
    {
        return $this->getSource()->getType();
    }

    public function getTargetType(): string
    {
        return $this->target->getType();
    }

    /**
     * Returns whether this event is of the given types.
     */
    public function isType(string $source, string $target): bool
    {
        return $source === $this->getSourceType()
            && $target === $this->getTargetType();
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    public function setForm(?FormInterface $form): void
    {
        $this->form = $form;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }
}
