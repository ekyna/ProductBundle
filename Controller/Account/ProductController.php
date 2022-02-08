<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Account;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Service\Search\ProductRepository;
use Ekyna\Component\Resource\Search\Request as SearchRequest;
use Ekyna\Component\Resource\Search\Search;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ProductController
 * @package Ekyna\Bundle\ProductBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductController
{
    private AuthorizationCheckerInterface $authorization;
    private Search                        $search;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Search                        $search
    ) {
        $this->authorization = $authorization;
        $this->search = $search;
    }

    public function search(Request $request): Response
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        $repository = $this->search->getRepository('ekyna_product.product');
        if (!$repository instanceof ProductRepository) {
            throw new UnexpectedTypeException($repository, ProductRepository::class);
        }

        $page = $request->query->getInt('page', 1) - 1;
        $limit = $request->query->getInt('limit', 10);
        $expression = (string)$request->query->get('query');

        $searchRequest = new SearchRequest($expression);
        $searchRequest
            ->setType(SearchRequest::RAW)
            ->setLimit($limit)
            ->setOffset($page * $limit)
            ->setParameter('types', (array)$request->query->get('types', []));

        if (!$repository->supports($searchRequest)) {
            return new JsonResponse([
                'results'     => [],
                'total_count' => 0,
            ]);
        }

        return new JsonResponse($repository->search($searchRequest));
    }
}
