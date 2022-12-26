<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Stock\StockAdjustmentDataType;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Stock\BundleStockAdjuster;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentData;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdjustStockAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdjustStockAction extends AbstractAction implements AdminActionInterface
{
    use FormTrait;
    use HelperTrait;
    use TemplatingTrait;
    use BreadcrumbTrait;

    public function __construct(
        private readonly BundleStockAdjuster    $adjuster,
        private readonly EntityManagerInterface $manager
    ) {
    }

    public function __invoke(): Response
    {
        $product = $this->context->getResource();
        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        if (!ProductTypes::isBundleType($product)) {
            throw new UnexpectedValueException('Expected bundle product');
        }

        $adjustment = new StockAdjustmentData($product);

        $form = $this->createForm(StockAdjustmentDataType::class, $adjustment, [
            'validation_groups' => ['Default', 'BundleAdjustment']
        ]);

        FormUtil::addFooter($form, [
            'cancel_path' => $this->generateResourcePath($this->context->getResource()),
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->adjuster->apply($adjustment);

            $this->manager->flush();

            return new RedirectResponse(
                $this->generateResourcePath($product)
            );
        }

        $this->breadcrumbFromContext($this->context);

        $config = $this->context->getConfig();

        $content = $this->twig->render('@EkynaProduct/Admin/Product/bundle_adjust_stock.html.twig', [
            'context'                   => $this->context,
            $config->getCamelCaseName() => $this->context->getResource(),
            'form'                      => $form->createView(),
        ]);

        return new Response($content);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_adjust_stock',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_adjust_stock',
                'path'     => '/adjust-stock',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'product.button.adjust_stock',
                'trans_domain' => 'EkynaProduct',
                'theme'        => 'primary',
                'icon'         => 'fa fa-tasks',
            ],
        ];
    }
}
