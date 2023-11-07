<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AdjustmentType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProductAdjustmentType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAdjustmentType extends AdjustmentType
{
    private ?array $designations = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly array               $config,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('designations', $this->getDesignations());
    }

    private function getDesignations(): array
    {
        if ($this->designations) {
            return $this->designations;
        }

        $this->designations = [];

        foreach ($this->config as $value => $entry) {
            $label = $this->translator->trans($entry['label'], [], $entry['domain']);

            $this->designations[$label] = $value;
        }

        return $this->designations;
    }
}
