<?php

namespace AppBundle\Controller;

use AppBundle\Elastic\Repository as ElasticRepository;
use Elastica\Document as ElasticDocument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteController extends Controller
{
    /**
     * @SWG\Delete(
     *   path="/models/{uuid}",
     *   summary="Delete a model",
     *   tags={"Models"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Model uuid to delete",
     *     in="path",
     *     name="uuid",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Model deleted"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="Model not found"
     *   )
     * )
     * @Route("/models/{uuid}", name="delete_model")
     * @Method({"DELETE"})
     * @ParamConverter("elasticDocument", class="Elastica:Document", converter="read_one_param_converter")
     */
    public function deleteAction(ElasticDocument $elasticDocument)
    {
        $elasticRepo = $this->get('elastic_repository');
        assert($elasticRepo instanceof ElasticRepository);

        $deleted = $elasticRepo->delete($elasticDocument);

        $statusCode = $deleted ? 200 : 500;

        $jsonResponse = new JsonResponse();
        $jsonResponse->setStatusCode($statusCode);
        $jsonResponse->setData($elasticDocument->getData());

        return $jsonResponse;
    }
}
