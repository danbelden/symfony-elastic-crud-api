<?php

namespace Tests\AppBundle\Mocks;

use Elastica\Document as ElasticDocument;
use Ramsey\Uuid\Uuid;

class MockElasticDocument extends ElasticDocument
{
    /**
     * MockElasticDocument constructor.
     *
     * {@inheritdoc}
     */
    public function __construct($id = '', $data = [], $type = '', $index = '')
    {
        if (empty($id)) {
            $uuidObj = Uuid::uuid4();
            $id = $uuidObj->toString();
        }

        parent::__construct($id, $data, $type, $index);

        if (empty($data)) {
            $this->setMockData();
        }
    }

    /**
     * Mock data method to initialise the document with data if none is set
     */
    private function setMockData()
    {
        $this->setData([
            'class' => ['model'],
            'properties' => [
                'uuid' => $this->getId(),
                'name' => 'test'
            ],
            'links' => [
                [
                    'rel' => ['model', 'self'],
                    'href' => '/models/' . $this->getId()
                ]
            ]
        ]);
    }
}
