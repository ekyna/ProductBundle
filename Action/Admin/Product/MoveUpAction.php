<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MoveUpAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MoveUpAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use ManagerTrait;

    public function __invoke(): Response
    {
        $variant = $this->context->getResource();
        if (!$variant instanceof ProductInterface) {
            throw new UnexpectedTypeException($variant, ProductInterface::class);
        }

        if (!ProductTypes::isVariantType($variant)) {
            return $this->redirect($this->generateResourcePath($variant));
        }

        $variable = $variant->getParent();

        if (0 === $variant->getPosition()) {
            return $this->redirect($this->generateResourcePath($variable));
        }

        $manager = $this->getManager();

        $variants = $variable->getVariants();

        $swapPosition = $variant->getPosition() - 1;
        foreach ($variants as $swap) {
            if ($swap->getPosition() === $swapPosition) {
                $swap->setPosition($swap->getPosition() + 1);
                $manager->persist($swap);

                $variant->setPosition($variant->getPosition() - 1);
                $manager->persist($variant);

                $manager->flush();
                break;
            }
        }

        return $this->redirect($this->generateResourcePath($variable));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_move_up',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_move_up',
                'path'     => '/move-up',
                'methods'  => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.move_up',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'primary',
                'icon'         => 'arrow-up',
            ],
        ];
    }
}
