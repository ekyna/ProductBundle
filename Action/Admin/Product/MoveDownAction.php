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
 * Class MoveDownAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MoveDownAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use ManagerTrait;

    public function __invoke(): Response
    {
        /** @var ProductInterface $variant */
        $variant = $this->context->getResource();
        if (!$variant instanceof ProductInterface) {
            throw new UnexpectedTypeException($variant, ProductInterface::class);
        }

        if (!ProductTypes::isVariantType($variant)) {
            return $this->redirect($this->generateResourcePath($variant));
        }

        $manager = $this->getManager();

        $variable = $variant->getParent();
        $variants = $variable->getVariants();

        $highest = 0;
        /** @var ProductInterface $v */
        foreach ($variants as $v) {
            if ($highest < $v->getPosition()) {
                $highest = $v->getPosition();
            }
        }

        if ($variant->getPosition() === $highest) {
            return $this->redirect($this->generateResourcePath($variable));
        }

        $swapPosition = $variant->getPosition() + 1;
        /** @var ProductInterface $swap */
        foreach ($variants as $swap) {
            if ($swap->getPosition() === $swapPosition) {
                $swap->setPosition($swap->getPosition() - 1);
                $manager->persist($swap);

                $variant->setPosition($variant->getPosition() + 1);
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
            'name'       => 'product_product_move_down',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_move_down',
                'path'     => '/move-down',
                'methods'  => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.move_down',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'primary',
                'icon'         => 'arrow-down',
            ],
        ];
    }
}
