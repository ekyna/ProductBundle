<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Account;

use Ekyna\Bundle\ProductBundle\Service\Search\ProductRepository;
use Elastica\Client as ElasticaClient;
use Elastica\Result;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
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
     * @var RepositoryManagerInterface
     */
    private $manager;

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
     * @param RepositoryManagerInterface    $manager
     * @param ElasticaClient                $client
     * @param string                        $productClass
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        RepositoryManagerInterface $manager,
        ElasticaClient $client,
        string $productClass
    ) {
        $this->authorization = $authorization;
        $this->manager = $manager;
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

        $page = intval($request->query->get('page', 1)) - 1;
        $limit = intval($request->query->get('limit', 10));

        $repository = $this->manager->getRepository($this->productClass);
        if (!$repository instanceOf ProductRepository) {
            throw new \RuntimeException('Expected instance of ' . ProductRepository::class);
        }

        $query = trim($request->query->get('query'));
        $types = $request->query->get('types', []);

        $query = $repository
            ->createSearchQuery($query, $types, false)
            ->setFrom($limit * $page)
            ->setSize($limit);

        $results = $this
            ->client
            ->getIndex('ekyna_product_product')
            ->getType('doc')
            ->search($query);

        $data = [
            'results'     => array_map(function (Result $result) {
                return $result->getSource();
            }, $results->getResults()),
            'total_count' => $results->getTotalHits(),
        ];

        return new JsonResponse($data);
    }
}
