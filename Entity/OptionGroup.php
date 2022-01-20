<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class OptionGroup
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\OptionGroupTranslationInterface translate(string $locale = null, bool $create = false)
 *
 * @TODO    Rename to 'Option'
 */
class OptionGroup extends RM\AbstractTranslatable implements Model\OptionGroupInterface
{
    use RM\SortableTrait;

    protected ?int                    $id        = null;
    protected ?Model\ProductInterface $product   = null;
    protected ?string                 $name      = null;
    protected bool                    $required  = false;
    protected bool                    $fullTitle = false;
    /** @var Collection<Model\OptionInterface> */
    protected Collection $options;

    public function __construct()
    {
        parent::__construct();

        $this->options = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?: 'New option group';
    }

    public function __clone()
    {
        parent::__clone();

        $this->product = null;
    }

    public function onCopy(CopierInterface $copier): void
    {
        $copier->copyCollection($this, 'options', true);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Model\ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?Model\ProductInterface $product): Model\OptionGroupInterface
    {
        if ($this->product === $product) {
            return $this;
        }

        if ($previous = $this->product) {
            $this->product = null;
            $previous->removeOptionGroup($this);
        }

        if ($this->product = $product) {
            $this->product->addOptionGroup($this);
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Model\OptionGroupInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): Model\OptionGroupInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): Model\OptionGroupInterface
    {
        $this->required = $required;

        return $this;
    }

    public function isFullTitle(): bool
    {
        return $this->fullTitle;
    }

    public function setFullTitle(bool $full): Model\OptionGroupInterface
    {
        $this->fullTitle = $full;

        return $this;
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function hasOption(Model\OptionInterface $option): bool
    {
        return $this->options->contains($option);
    }

    public function addOption(Model\OptionInterface $option): Model\OptionGroupInterface
    {
        if (!$this->hasOption($option)) {
            $this->options->add($option);
            $option->setGroup($this);
        }

        return $this;
    }

    public function removeOption(Model\OptionInterface $option): Model\OptionGroupInterface
    {
        if ($this->hasOption($option)) {
            $this->options->removeElement($option);
            $option->setGroup(null);
        }

        return $this;
    }

    public function setOptions(Collection $options): Model\OptionGroupInterface
    {
        $this->options = $options;

        return $this;
    }

    protected function getTranslationClass(): string
    {
        return OptionGroupTranslation::class;
    }
}
