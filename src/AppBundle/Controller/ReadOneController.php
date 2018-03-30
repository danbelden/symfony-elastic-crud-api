<?php

namespace AppBundle\Controller;

use Elastica\Document as ElasticDocument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReadOneController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/models/{uuid}",
     *   summary="Fetch a model",
     *   tags={"Models"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Model uuid to fetch",
     *     in="path",
     *     name="uuid",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Model found"
     *   ),
     *   @SWG\Response(
     *     response="400",
     *     description="Invalid request"
     *   )
     * )
     * @Method({"GET"})
     * @ParamConverter("elasticDocument", class="Elastica:Document", converter="read_one_param_converter")
     * @Route("/models/{uuid}", name="read_one_model")
     * @param ElasticDocument $elasticDocument
     */
    public function readOneAction(ElasticDocument $elasticDocument)
    {
        $docData = $elasticDocument->getData();

        $jsonResponse = new JsonResponse();
        $jsonResponse->setData($docData);

        return $jsonResponse;
    }
}
