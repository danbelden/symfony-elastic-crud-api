<?php

namespace Tests\AppBundle\Mocks;

use AppBundle\ParamConverter\Read as ReadParamConverter;
use Elastica\Document as ElasticDocument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MockReadParamConverter extends ReadParamConverter
{
    /**
     * @var ElasticDocument
     */
    private $document;

    /**
     * Constructor
     *
     * @param ElasticDocument $elasticDocument
     */
    public function __construct(ElasticDocument $elasticDocument = null)
    {
        $this->document = $elasticDocument;
    }

    /**
     * Mock support to always true for tests
     *
     * @param ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        return true;
    }

    /**
     * Mock apply to add the given constructor elastic document if provided
     *
     * @param Request $request
     * @param ParamConverter $configuration
     * @throws LogicException
     * @throws NotFoundHttpException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $uuid = $request->get('uuid');
        if (empty($uuid)) {
            throw new LogicException('No request uuid found');
        }

        $class = $configuration->getClass();
        if (strlen($uuid) !== 36) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        if ($this->document !== null) {
            $name = $configuration->getName();
            $request->attributes->set($name, $this->document);
        }
    }
}
