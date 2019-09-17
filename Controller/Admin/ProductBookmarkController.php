<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Security\UserProviderInterface;
use Ekyna\Bundle\ProductBundle\Entity\ProductBookmark;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductBookmarkRepository;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
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
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductBookmarkRepository
     */
    private $bookmarkRepository;

    /**
     * @var RegistryInterface
     */
    private $doctrine;


    /**
     * Constructor.
     *
     * @param UserProviderInterface      $userProvider
     * @param ProductRepositoryInterface $productRepository
     * @param ProductBookmarkRepository  $bookmarkRepository
     * @param RegistryInterface          $doctrine
     */
    public function __construct(
        UserProviderInterface $userProvider,
        ProductRepositoryInterface $productRepository,
        ProductBookmarkRepository $bookmarkRepository,
        RegistryInterface $doctrine
    ) {
        $this->userProvider = $userProvider;
        $this->productRepository = $productRepository;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->doctrine = $doctrine;
    }

    /**
     * Adds the user's product bookmark.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function add(Request $request): Response
    {
        $user = $this->getAdmin();
        $product = $this->getProduct($request->attributes->get('productId'));

        $bookmark = $this->bookmarkRepository->findBookmark($user, $product);

        if ($bookmark) {
            return new Response();
        }

        $bookmark = new ProductBookmark();
        $bookmark
            ->setUser($user)
            ->setProduct($product);

        $em = $this->doctrine->getEntityManagerForClass(ProductBookmark::class);
        $em->persist($bookmark);
        $em->flush();

        return new Response();
    }

    /**
     * Removes the user's product bookmark.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function remove(Request $request): Response
    {
        $user = $this->getAdmin();
        $product = $this->getProduct($request->attributes->get('productId'));

        $bookmark = $this->bookmarkRepository->findBookmark($user, $product);
        if (!$bookmark) {
            return new Response('Not found', Response::HTTP_NOT_FOUND);
        }

        $em = $this->doctrine->getEntityManagerForClass(ProductBookmark::class);
        $em->remove($bookmark);
        $em->flush();

        return new Response();
    }

    private function getProduct(int $id): ProductInterface
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException("Product not found");
        }

        return $product;
    }

    private function getAdmin(): UserInterface
    {
        if (!$this->userProvider->hasUser()) {
            throw new AccessDeniedHttpException("User not found");
        }

        return $this->userProvider->getUser();
    }
}
