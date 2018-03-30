<?php

namespace AppBundle\Controller;

use Elastica\Document as ElasticDocument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateController extends Controller
{
    /**
     * @SWG\Post(
     *   path="/models",
     *   summary="Create a model",
     *   tags={"Models"},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Model",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/Create"),
     *   ),
     *   @SWG\Response(
     *     response="201",
     *     description="Model created"
     *   ),
     *   @SWG\Response(
     *     response="400",
     *     description="Invalid request"
     *   )
     * )
     * @Route("/models", name="create_model")
     * @ParamConverter("elasticDocument", class="Elastica:Document", converter="create_param_converter")
     * @Method({"POST"})
     */
    public function createAction(ElasticDocument $elasticDocument)
    {
        $docData = $elasticDocument->getData();

        $jsonResponse = new JsonResponse(
            $docData,
            201,
            [
                'uuid' => $elasticDocument->getId()
            ],
            true
        );

        return $jsonResponse;
    }
}
