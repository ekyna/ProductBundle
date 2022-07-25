<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Event\ConvertEvent;
use Ekyna\Bundle\ProductBundle\Exception\ConvertException;
use Ekyna\Bundle\ProductBundle\Factory\ProductFactoryInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

use function array_key_exists;
use function array_map;
use function implode;
use function iterator_to_array;
use function sprintf;

/**
 * Class AbstractConverter
 * @package Ekyna\Bundle\ProductBundle\Service\Converter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConverter implements ConverterInterface
{
    protected ?ProductInterface $source = null;
    protected ?ProductInterface $target = null;
    private ?ConvertEvent       $event  = null;
    private ?FormInterface      $form   = null;
    private array               $data   = [];

    public function __construct(
        protected readonly ProductFactoryInterface          $productFactory,
        protected readonly ResourceManagerInterface         $productManager,
        protected readonly EntityManagerInterface           $entityManager,
        protected readonly FormFactoryInterface             $formFactory,
        protected readonly RequestStack                     $requestStack,
        protected readonly ValidatorInterface               $validator,
        protected readonly ResourceEventDispatcherInterface $eventDispatcher,
        protected readonly OfferInvalidator                 $offerInvalidator
    ) {
    }

    /**
     * Returns the latest event.
     */
    public function getEvent(): ?ConvertEvent
    {
        return $this->event;
    }

    /**
     * Returns the convert form.
     */
    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    /**
     * @inheritDoc
     */
    public function convert(ProductInterface $source): ConvertEvent
    {
        $this->clear();

        if (!$this->supportsSourceType($type = $source->getType())) {
            throw new ConvertException("Source type '$type' is not supported");
        }

        $this->source = $source;

        try {
            $this->target = $this->init();

            $this->dispatch(ConvertEvent::FORM_DATA);

            $this->form = $this->buildForm();

            $this->form->handleRequest($this->requestStack->getMainRequest());

            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $this->onPreConvert();

                $this->dispatch(ConvertEvent::PRE_CONVERT);

                $this->onConvert();

                $this->dispatch(ConvertEvent::POST_CONVERT);

                $this->validateAndPersist();

                $this->form = null;

                $this->dispatch(ConvertEvent::DONE_CONVERT);

                $this->onDoneConvert();

                $this->event->setSuccess(true);
            }
        } catch (ConvertException) {
            $this->onError();
        }

        $this->event->setForm($this->form);

        return $this->event;
    }

    /**
     * Initializes the conversion.
     */
    abstract protected function init(): ProductInterface;

    /**
     * Builds the target form.
     */
    abstract protected function buildForm(): FormInterface;

    /**
     * Pre convert.
     */
    protected function onPreConvert(): void
    {
        $this->target
            ->setQuoteOnly(true)
            ->setPendingOffers(true)
            ->setPendingPrices(true);

        $this->offerInvalidator->invalidateByProduct($this->target);
    }

    /**
     * Convert.
     */
    abstract protected function onConvert(): void;

    /**
     * Done convert.
     */
    protected function onDoneConvert(): void
    {
    }

    /**
     * Validates and persists the target.
     */
    protected function validateAndPersist(): void
    {
        $sourceViolations = [];
        if ($this->source !== $this->target) {
            $sourceViolations = iterator_to_array(
                $this->validator->validate($this->source, null, ['Default', $this->source->getType()])
            );
        }

        $targetViolations = iterator_to_array(
            $this->validator->validate($this->target, null, ['Default', $this->target->getType()])
        );

        if (!empty($targetViolations) || !empty($sourceViolations)) {
            $this->event = $this->createEvent();

            /** @var ConstraintViolationInterface $violation */
            foreach ($sourceViolations as $violation) {
                $this->event->addMessage(new ResourceMessage(
                    sprintf('[Source] (%s) %s', $violation->getPropertyPath(), $violation->getMessage()),
                    ResourceMessage::TYPE_ERROR
                ));
            }

            /** @var ConstraintViolationInterface $violation */
            foreach ($targetViolations as $violation) {
                $this->event->addMessage(new ResourceMessage(
                    sprintf('[Target] (%s) %s', $violation->getPropertyPath(), $violation->getMessage()),
                    ResourceMessage::TYPE_ERROR
                ));
            }

            $this->eventToException();

            return;
        }

        try {
            if ($this->source === $this->target) {
                $event = $this->productManager->update($this->target);
            } else {
                $event = $this->productManager->create($this->target);
            }
        } catch (Throwable $e) {
            throw new ConvertException('Persistence failed', 0, $e);
        }

        $this->createEvent();
        foreach ($event->getMessages() as $message) {
            $this->event->addMessage($message);
        }

        $this->eventToException();
    }

    /**
     * Error handler.
     */
    protected function onError(): void
    {
    }

    /**
     * Stores data.
     */
    protected function set(string $key, mixed $data): void
    {
        $this->data[$key] = $data;
    }

    /**
     * Retrieves data.
     */
    protected function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->data)) {
            throw new ConvertException("No data for key '$key'.");
        }

        return $this->data[$key];
    }

    /**
     * Clears conversion elements.
     */
    private function clear(): void
    {
        $this->event = null;
        $this->form = null;
        $this->data = [];
    }

    /**
     * Dispatches the convert event.
     */
    private function dispatch(string $name): void
    {
        $this->event = $this->createEvent();

        $this->eventDispatcher->dispatch($this->event, $name);

        $this->eventToException();
    }

    /**
     * Throw a convert exception if event has error(s).
     *
     * @throws ConvertException
     */
    private function eventToException(): void
    {
        if (!$this->event->hasErrors()) {
            return;
        }

        $message = implode("\n", array_map(function (ResourceMessage $message) {
            return $message->getMessage();
        }, $this->event->getMessages()));

        throw new ConvertException($message);
    }

    /**
     * Creates a convert event.
     */
    private function createEvent(): ConvertEvent
    {
        $event = new ConvertEvent($this->source, $this->target);

        $event->setForm($this->form);

        return $event;
    }
}
