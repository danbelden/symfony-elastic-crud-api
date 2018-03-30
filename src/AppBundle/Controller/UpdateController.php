<?php

namespace AppBundle\Controller;

use Elastica\Document as ElasticDocument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateController extends Controller
{
    /**
     * @SWG\Post(
     *   path="/models/{uuid}",
     *   summary="Update a model",
     *   tags={"Models"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Model uuid to update",
     *     in="path",
     *     name="uuid",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Model",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/Update"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Model updated"
     *   ),
     *   @SWG\Response(
     *     response="400",
     *     description="Invalid request"
     *   )
     * )
     * @Route("/models/{uuid}", name="update_model")
     * @Method({"POST"})
     * @ParamConverter("elasticDocument", class="Elastica:Document", converter="update_param_converter")
     */
    public function updateAction(ElasticDocument $elasticDocument)
    {
        $docData = $elasticDocument->getData();

        $jsonResponse = new JsonResponse();
        $jsonResponse->setData($docData);

        return $jsonResponse;
    }
}
