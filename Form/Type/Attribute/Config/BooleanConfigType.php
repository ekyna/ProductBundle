<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config;

use Ekyna\Bundle\ProductBundle\Attribute\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class BooleanConfigType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BooleanConfigType extends AbstractType
{
    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var string
     */
    private $defaultLocale;


    /**
     * Constructor.
     *
     * @param string[] $locales
     * @param string   $defaultLocale
     */
    public function __construct(array $locales, string $defaultLocale)
    {
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $trueLabels = $builder
            ->create(BooleanType::TRUE, FormType::class, [
                'label'    => 'ekyna_core.value.yes',
                'required' => false,
            ]);

        $falseLabels = $builder
            ->create(BooleanType::FALSE, FormType::class, [
                'label'    => 'ekyna_core.value.no',
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
