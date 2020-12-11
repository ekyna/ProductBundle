<?php

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Event\ConvertEvent;
use Ekyna\Bundle\ProductBundle\Exception\ConvertException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

/**
 * Class AbstractConverter
 * @package Ekyna\Bundle\ProductBundle\Service\Converter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConverter implements ConverterInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $productManager;

    /**
     * @var ResourceOperatorInterface
     */
    protected $productOperator;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ResourceEventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ProductInterface
     */
    protected $source;

    /**
     * @var ProductInterface|null
     */
    protected $target;

    /**
     * @var ConvertEvent
     */
    private $event;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var array
     */
    private $data;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface       $productRepository
     * @param EntityManagerInterface           $productManager
     * @param ResourceOperatorInterface        $productOperator
     * @param FormFactoryInterface             $formFactory
     * @param RequestStack                     $requestStack
     * @param ValidatorInterface               $validator
     * @param ResourceEventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EntityManagerInterface $productManager,
        ResourceOperatorInterface $productOperator,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        ValidatorInterface $validator,
        ResourceEventDispatcherInterface $eventDispatcher
    ) {
        $this->productRepository = $productRepository;
        $this->productManager = $productManager;
        $this->productOperator = $productOperator;
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns the latest event.
     *
     * @return ConvertEvent|null
     */
    public function getEvent(): ?ConvertEvent
    {
        return $this->event;
    }

    /**
     * Returns the convert form.
     *
     * @return FormInterface|null
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

            $this->form->handleRequest($this->requestStack->getMasterRequest());

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
        } catch (ConvertException $e) {
            $this->onError();
        }

        $this->event->setForm($this->form);

        return $this->event;
    }

    /**
     * Initializes the conversion.
     *
     * @return ProductInterface The target product
     */
    abstract protected function init();

    /**
     * Builds the target form.
     *
     * @return FormInterface
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
                $event = $this->productOperator->update($this->target);
            } else {
                $event = $this->productOperator->create($this->target);
            }
        } catch (Throwable $e) {
            throw new ConvertException("Persistence failed", 0, $e);
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
     *
     * @param string $key
     * @param mixed  $data
     */
    protected function set(string $key, $data): void
    {
        $this->data[$key] = $data;
    }

    /**
     * Retrieves data.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function get(string $key)
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
     *
     * @param string $name
     */
    private function dispatch(string $name): void
    {
        $this->event = $this->createEvent();

        $this->eventDispatcher->dispatch($name, $this->event);

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
     *
     * @return ConvertEvent
     */
    private function createEvent(): ConvertEvent
    {
        $event = new ConvertEvent($this->source, $this->target);

        $event->setForm($this->form);

        return $event;
    }
}
