<?php

namespace Tests\AppBundle\Mocks;

use AppBundle\ParamConverter\Update as UpdateParamConverter;
use Elastica\Document as ElasticDocument;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MockUpdateParamConverter extends UpdateParamConverter
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
    public function setDocument(ElasticDocument $elasticDocument)
    {
        $this->document = $elasticDocument;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDocument(string $uuid): ElasticDocument
    {
        $exists = strlen($uuid) === 36 && $this->document !== null;
        if ($exists === false) {
            $excMsg = sprintf('Document `%s` does not exist', $uuid);
            throw new BadRequestHttpException($excMsg);
        }

        return $this->document;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateDocumentInStorage(ElasticDocument $elasticDocument): bool
    {
        return true;
    }
}
