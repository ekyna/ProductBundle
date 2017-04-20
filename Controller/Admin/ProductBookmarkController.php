<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\ProductBundle\Entity\ProductBookmark;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductBookmarkRepository;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductBookmarkController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductBookmarkController
{
    private UserProviderInterface      $userProvider;
    private ProductRepositoryInterface $productRepository;
    private ProductBookmarkRepository  $bookmarkRepository;
    private ManagerRegistry            $doctrine;

    public function __construct(
        UserProviderInterface      $userProvider,
        ProductRepositoryInterface $productRepository,
        ProductBookmarkRepository  $bookmarkRepository,
        ManagerRegistry            $doctrine
    ) {
        $this->userProvider = $userProvider;
        $this->productRepository = $productRepository;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->doctrine = $doctrine;
    }

    /**
     * Adds the user's product bookmark.
     */
    public function add(Request $request): Response
    {
        $user = $this->getAdmin();
        $product = $this->getProduct($request->attributes->getInt('productId'));

        $bookmark = $this->bookmarkRepository->findBookmark($user, $product);
        if ($bookmark) {
            return (new Response())->setPrivate();
        }

        $bookmark = new ProductBookmark();
        $bookmark
            ->setUser($user)
            ->setProduct($product);

        $em = $this->doctrine->getManagerForClass(ProductBookmark::class);
        $em->persist($bookmark);
        $em->flush();

        return (new Response())->setPrivate();
    }

    /**
     * Removes the user's product bookmark.
     */
    public function remove(Request $request): Response
    {
        $user = $this->getAdmin();
        $product = $this->getProduct($request->attributes->getInt('productId'));

        $bookmark = $this->bookmarkRepository->findBookmark($user, $product);
        if (!$bookmark) {
            return (new Response('Not found', Response::HTTP_NOT_FOUND))->setPrivate();
        }

        $em = $this->doctrine->getManagerForClass(ProductBookmark::class);
        $em->remove($bookmark);
        $em->flush();

        return (new Response())->setPrivate();
    }

    private function getProduct(int $id): ProductInterface
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException('Product not found');
        }

        return $product;
    }

    private function getAdmin(): UserInterface
    {
        if (!$this->userProvider->hasUser()) {
            throw new AccessDeniedHttpException('User not found');
        }

        $user = $this->userProvider->getUser();

        if (!$user instanceof UserInterface) {
            throw new UnexpectedTypeException($user, UserInterface::class);
        }

        return $user;
    }
}
