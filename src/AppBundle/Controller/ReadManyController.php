<?php

namespace AppBundle\Controller;

use AppBundle\Criteria\Model as ModelCriteria;
use AppBundle\Elastic\QueryBuilder;
use AppBundle\Elastic\Repository as ElasticRepository;
use AppBundle\Handler\Models as ModelsHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Siren\Handler as SirenHandler;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ReadManyController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/models",
     *   summary="List models",
     *   tags={"Models"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     type="string",
     *     name="name",
     *     in="query",
     *     description="Name of model to retrieve",
     *     required=false
     *   ),
     *   @SWG\Parameter(
     *     type="integer",
     *     name="limit",
     *     in="query",
     *     description="Number of models to read",
     *     required=false
     *   ),
     *   @SWG\Parameter(
     *     type="integer",
     *     name="offset",
     *     in="query",
     *     description="Offset to read first result from",
     *     required=false
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="An example resource"
     *   ),
     *   @SWG\Response(
     *     response="400",
     *     description="Invalid request"
     *   )
     * )
     * @Method({"GET"})
     * @Route("/models", name="read_many_model")
     * @ParamConverter("criteria", class="AppBundle\Criteria\Model", converter="read_many_param_converter")
     * @param ModelCriteria $criteria
     * @param Request $request
     */
    public function readManyAction(ModelCriteria $criteria)
    {
        $queryBuilder = new QueryBuilder();
        $query = $queryBuilder->build($criteria);

        $elasticRepo = $this->get('elastic_repository');
        assert($elasticRepo instanceof ElasticRepository);
        $resultSet = $elasticRepo->search($query);

        $modelsHandler = $this->get('models_handler');
        assert($modelsHandler instanceof ModelsHandler);

        $sirenDocument = $modelsHandler->toDocument($criteria, $resultSet);

        $sirenHandler = new SirenHandler();
        $jsonString = $sirenHandler->toJson($sirenDocument);

        $jsonResponse = new JsonResponse();
        $jsonResponse->setStatusCode(200);
        $jsonResponse->setJson($jsonString);

        return $jsonResponse;
    }
}
