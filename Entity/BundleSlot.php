<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Model\AbstractTranslatable;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class BundleSlot
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\BundleSlotTranslationInterface translate(string $locale = null, bool $create = false)
 */
class BundleSlot extends AbstractTranslatable implements Model\BundleSlotInterface
{
    use MediaSubjectTrait;
    use SortableTrait;

    protected ?Model\ProductInterface $bundle = null;
    protected bool                    $required = true;
    /** @var Collection<Model\BundleChoiceInterface> */
    protected Collection $choices;
    /** @var Collection<Model\BundleSlotRuleInterface> */
    protected Collection $rules;

    public function __construct()
    {
        parent::__construct();

        $this->choices = new ArrayCollection();
        $this->rules = new ArrayCollection();
    }

    public function __clone()
    {
        parent::__clone();

        $this->bundle = null;
    }

    public function onCopy(CopierInterface $copier): void
    {
        $copier->copyCollection($this, 'choices', true);
        $copier->copyCollection($this, 'rules', true);
    }

    public function getBundle(): ?Model\ProductInterface
    {
        return $this->bundle;
    }

    public function setBundle(?Model\ProductInterface $bundle): Model\BundleSlotInterface
    {
        if ($this->bundle !== $bundle) {
            if ($previous = $this->bundle) {
                $this->bundle = null;
                $previous->removeBundleSlot($this);
            }

            if ($this->bundle = $bundle) {
                $this->bundle->addBundleSlot($this);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): Model\BundleSlotInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->translate()->getDescription();
    }

    public function setDescription(?string $description): Model\BundleSlotInterface
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function hasChoice(Model\BundleChoiceInterface $choice): bool
    {
        return $this->choices->contains($choice);
    }

    public function addChoice(Model\BundleChoiceInterface $choice): Model\BundleSlotInterface
    {
        if (!$this->hasChoice($choice)) {
            $this->choices->add($choice);
            $choice->setSlot($this);
        }

        return $this;
    }

    public function removeChoice(Model\BundleChoiceInterface $choice): Model\BundleSlotInterface
    {
        if ($this->hasChoice($choice)) {
            $this->choices->removeElement($choice);
            $choice->setSlot(null);
        }

        return $this;
    }

    public function setChoices(Collection $choices): Model\BundleSlotInterface
    {
        $this->choices = $choices;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): Model\BundleSlotInterface
    {
        $this->required = $required;

        return $this;
    }

    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function hasRule(Model\BundleSlotRuleInterface $rule): bool
    {
        return $this->rules->contains($rule);
    }

    public function addRule(Model\BundleSlotRuleInterface $rule): Model\BundleSlotInterface
    {
        if (!$this->hasRule($rule)) {
            $this->rules->add($rule);
            $rule->setSlot($this);
        }

        return $this;
    }

    public function removeRule(Model\BundleSlotRuleInterface $rule): Model\BundleSlotInterface
    {
        if ($this->hasRule($rule)) {
            $this->rules->removeElement($rule);
            $rule->setSlot(null);
        }

        return $this;
    }

    public function setRules(Collection $rules): Model\BundleSlotInterface
    {
        $this->rules = $rules;

        return $this;
    }
}
