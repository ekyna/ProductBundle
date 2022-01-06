<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\CopierTrait;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DuplicateAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DuplicateAction extends AbstractAction implements AdminActionInterface
{
    use CopierTrait;
    use FormTrait;
    use HelperTrait;
    use ManagerTrait;
    use FlashTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        // Source
        $source = $this->context->getResource();
        if (!$source instanceof ProductInterface) {
            throw new UnexpectedTypeException($source, ProductInterface::class);
        }

        // TODO Temporary lock
        if ($source->getType() !== ProductTypes::TYPE_SIMPLE) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        $target = $this->copier->copyResource($source);

        $this->context->setResource($target);

        $type = $this->context->getConfig()->getData('form');

        $form = $this->createForm($type, $target, [
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            'admin_mode'        => true,
            '_redirect_enabled' => true,
            'action' => $this->generateResourcePath($source, self::class),
        ]);

        FormUtil::addFooter($form, [
            'cancel_path' => $this->generateResourcePath($source),
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($target->getTranslations() as $translation) {
                $translation->setSlug(null);
            }

            $event = $this->getManager()->save($target);

            if (!$event->hasErrors()) {
                return $this->redirect($this->generateResourcePath($target));
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context'       => $this->context,
            'source'        => $source,
            'form'          => $form->createView(),
            'form_template' => $this->options['form_template'],
        ])->setPrivate();
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_duplicate',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'     => 'admin_%s_duplicate',
                'path'     => '/duplicate',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.duplicate',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'duplicate',
            ],
            'options'    => [
                'template'      => '@EkynaProduct/Admin/Product/duplicate.html.twig',
                'form_template' => '@EkynaProduct/Admin/Product/_form.html.twig',
            ],
        ];
    }
}
