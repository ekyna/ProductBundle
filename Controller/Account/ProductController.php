<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Account;

use Ekyna\Bundle\ProductBundle\Service\Search\ProductRepository;
use Ekyna\Component\Resource\Search\Request as SearchRequest;
use Ekyna\Component\Resource\Search\Search;
use Elastica\Client as ElasticaClient;
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
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var Search
     */
    private $search;

    /**
     * @var ElasticaClient
     */
    private $client;

    /**
     * @var string
     */
    private $productClass;


    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param Search                        $search
     * @param ElasticaClient                $client
     * @param string                        $productClass
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Search $search,
        ElasticaClient $client,
        string $productClass
    ) {
        $this->authorization = $authorization;
        $this->search = $search;
        $this->client = $client;
        $this->productClass = $productClass;
    }

    /**
     * Account product search.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function search(Request $request): Response
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        $repository = $this->search->getRepository('ekyna_product.product');
        if (!$repository instanceof ProductRepository) {
            throw new \RuntimeException('Expected instance of ' . ProductRepository::class);
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
