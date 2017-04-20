<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\MediaBundle\Model\MediaInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;

use function addcslashes;
use function in_array;
use function sprintf;

/**
 * Class ProductRenderer
 * @package Ekyna\Bundle\ProductBundle\Service\Renderer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductHelper
{
    private ProductRepositoryInterface $productRepository;
    private string                     $defaultImage;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        string                     $defaultImage = '/bundles/ekynaproduct/img/no-image.gif'
    ) {
        $this->productRepository = $productRepository;
        $this->defaultImage = $defaultImage;
    }

    public function findOneProductById(int $id): ?ProductInterface
    {
        return $this->productRepository->findOneById($id);
    }

    public function findOneProductByReference(string $reference): ?ProductInterface
    {
        return $this->productRepository->findOneByReference($reference);
    }

    public function renderExternalReference(
        ProductInterface $product,
        string           $type = ProductReferenceTypes::TYPE_EAN_13
    ): ?string {
        return $product->getReferenceByType($type);
    }

    /**
     * Returns the main image for the given product.
     *
     * @TODO Refactor with \Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder::getProductImagePath
     */
    public function getProductImagePath(ProductInterface $product): ?MediaInterface
    {
        $images = $product->getMedias([MediaTypes::IMAGE]);

        if (0 == $images->count() && $product->getType() === ProductTypes::TYPE_VARIABLE) {
            /** @var ProductInterface $variant */
            $variant = $product->getVariants()->first();
            $images = $variant->getMedias([MediaTypes::IMAGE]);
        }

        if (0 < $images->count()) {
            /** @var ProductMediaInterface $image */
            $image = $images->first();

            return $image->getMedia();
        }

        return null;
    }

    /**
     * Returns the bundle visible products.
     *
     * @return array<ProductInterface>
     */
    public function getBundleVisibleProducts(ProductInterface $product): array
    {
        ProductTypes::assertBundle($product);

        $visible = [];

        foreach ($product->getBundleSlots() as $slot) {
            /** @var BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $choiceProduct = $choice->getProduct();
            if ($choiceProduct->isVisible() && !$choice->isHidden()) {
                $visible[] = [
                    'quantity' => $choice->getMinQuantity(),
                    'product'  => $choiceProduct,
                ];
            }
        }

        return $visible;
    }

    /**
     * Renders the bundle rule condition product.
     */
    public function getBundleRuleConditionProduct(array $condition, ProductInterface $bundle): ?ProductInterface
    {
        foreach ($bundle->getBundleSlots() as $si => $slot) {
            if ($si != $condition['slot']) {
                continue;
            }

            foreach ($slot->getChoices() as $ci => $choice) {
                if ($ci != $condition['choice']) {
                    continue;
                }

                return $choice->getProduct();
            }
        }

        return null;
    }

    /**
     * Renders the bundle choice option groups badge.
     */
    public function renderBundleChoiceOptionGroups(BundleChoiceInterface $choice): string
    {
        $product = $choice->getProduct();

        $excluded = $choice->getExcludedOptionGroups();
        $groups = $product->resolveOptionGroups([], true);

        $all = $exc = 0;
        $popover = '<ul class="list-unstyled">';
        foreach ($groups as $group) {
            $all++;
            if (in_array($group->getId(), $excluded)) {
                $icon = '<i class="fa fa-remove text-danger"/>';
                $exc++;
            } else {
                $icon = '<i class="fa fa-check text-success"/>';
            }

            $label = sprintf(
                '[%s] %s',
                $group->isRequired() ? 'Required' : 'Optional',
                addcslashes($group->getName(), "'")
            );

            $popover .= "<li>$icon $label</li>";
        }

        $popover .= "</ul>";

        if ($exc === 0) {
            $theme = 'success';
            $icon = '<i class="fa fa-check"/>';
        } elseif ($all === $exc) {
            $theme = 'danger';
            $icon = '<i class="fa fa-remove"/>';
        } else {
            $theme = 'warning';
            $icon = '<i class="fa fa-check"/>';
        }

        if ($all === 0) {
            $popover = '';
        } else {
            $popover = " data-toggle=\"popover\" data-content='$popover'";
        }

        /** @noinspection HtmlUnknownAttribute */
        return sprintf(
            '<span class="label label-%s"%s>%s</span>',
            $theme, $popover, $icon
        );
    }

    /**
     * Returns the default product image path.
     */
    public function getDefaultImage(): string
    {
        return $this->defaultImage;
    }
}
