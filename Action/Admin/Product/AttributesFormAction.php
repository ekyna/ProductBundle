<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ProductBundle\Form\ProductFormBuilder;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FactoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;

/**
 * Class AttributesFormAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AttributesFormAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use FactoryTrait;
    use RepositoryTrait;
    use FormTrait;
    use TemplatingTrait;

    private ProductFormBuilder $productFormBuilder;

    public function __construct(ProductFormBuilder $productFormBuilder)
    {
        $this->productFormBuilder = $productFormBuilder;
    }

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported');
        }

        // Product
        // TODO Find the product if edit
        /** @var ProductInterface $product */
        $product = $this->getFactory(ProductInterface::class)->create();
        $product->setType(ProductTypes::TYPE_SIMPLE);

        // Attribute Set
        /** @var AttributeSetInterface $attributeSet */
        $attributeSet = $this->getRepository(AttributeSetInterface::class)->find(
            $this->request->attributes->getInt('attributeSetId')
        );
        if (null === $attributeSet) {
            throw new NotFoundHttpException('Attribute set not found');
        }

        // Form
        $form = $this->formFactory->createNamed('FORM__NAME', FormType::class, $product, [
            'block_name' => 'product',
        ]);

        $this->productFormBuilder
            ->initialize($product, $form)
            ->addAttributesField($attributeSet);

        $response = $this->render('@EkynaProduct/Admin/Product/attributes_form.xml.twig', [
            'form' => $form->createView(),
        ]);

        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response->setPrivate();
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_attributes_form',
            'permission' => Permission::READ,
            'route'      => [
                'name'    => 'admin_%s_attributes_form',
                'path'    => '/attributes-form/{attributeSetId}',
                'methods' => ['GET'],
            ],
            'options'    => [
                'template' => '@EkynaProduct/Admin/Product/attributes_form.xml.twig',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'attributeSetId' => '\d+',
        ]);
    }
}
