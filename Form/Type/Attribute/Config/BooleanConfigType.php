<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config;

use Ekyna\Bundle\ProductBundle\Attribute\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use function Symfony\Component\Translation\t;

/**
 * Class BooleanConfigType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BooleanConfigType extends AbstractType
{
    private array  $locales;
    private string $defaultLocale;

    /**
     * @param string[] $locales
     */
    public function __construct(array $locales, string $defaultLocale)
    {
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $trueLabels = $builder
            ->create(BooleanType::TRUE, FormType::class, [
                'label'    => t('value.yes', [], 'EkynaUi'),
                'required' => false,
            ]);

        $falseLabels = $builder
            ->create(BooleanType::FALSE, FormType::class, [
                'label'    => t('value.no', [], 'EkynaUi'),
                'required' => false,
            ]);

        foreach ($this->locales as $locale) {
            $required = false;
            $constraints = [];
            if ($locale === $this->defaultLocale) {
                $required = true;
                $constraints = [new NotBlank()];
            }
            $trueLabels->add($locale, TextType::class, [
                'label'       => false,
                'required'    => $required,
                'constraints' => $constraints,
                'attr'        => [
                    'input_group' => [
                        'prepend' => strtoupper($locale),
                    ],
                ],
            ]);

            $falseLabels->add($locale, TextType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => [
                    'input_group' => [
                        'prepend' => strtoupper($locale),
                    ],
                ],
            ]);
        }

        $transformer = new CallbackTransformer(
            function ($array) {
                return $array;
            },
            function ($array) {
                foreach ($array as $value => &$labels) {
                    foreach ($labels as $locale => $label) {
                        if (is_null($label)) {
                            unset($labels[$locale]);
                        }
                    }
                    if (empty($labels)) {
                        unset($array[$value]);
                    }
                }

                return $array;
            }
        );

        $builder
            ->add($trueLabels)
            ->add($falseLabels)
            ->addModelTransformer($transformer);
    }
}
