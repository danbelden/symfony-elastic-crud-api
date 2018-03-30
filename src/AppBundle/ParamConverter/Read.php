<?php

namespace AppBundle\ParamConverter;

use AppBundle\Elastic\Repository as ElasticRepo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Read extends Base implements ParamConverterInterface
{
    /**
     * @var ElasticRepo
     */
    private $elasticRepo;

    /**
     * Constructor
     *
     * @param ElasticRepo $elasticRepo
     */
    public function __construct(ElasticRepo $elasticRepo)
    {
        $this->elasticRepo = $elasticRepo;
    }

    /**
     * Method to convert the request into a matching siren document
     *
     * @param Request $request
     * @param ParamConverter $configuration
     * @throws BadRequestHttpException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $uuid = $request->get('uuid');
        if ($uuid === null) {
            throw new BadRequestHttpException('Read requires the `uuid` parameter');
        }

        $exists = $this->elasticRepo->exists($uuid);
        if ($exists === false) {
            $excMsg = sprintf('Document `%s` does not exist', $uuid);
            throw new BadRequestHttpException($excMsg);
        }

        $elasticDocument = $this->elasticRepo->fetch($uuid);

        $request->attributes->set($configuration->getName(), $elasticDocument);
    }
}
