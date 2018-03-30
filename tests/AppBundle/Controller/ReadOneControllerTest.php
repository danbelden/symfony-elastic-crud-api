<?php

namespace Tests\AppBundle\Controller;

use Ramsey\Uuid\Uuid;
use Siren\Handler;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\Mocks\MockElasticDocument;
use Tests\AppBundle\Mocks\MockReadParamConverter;

class ReadOneControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Container
     */
    private $container;

    public function setup()
    {
        $this->client = static::createClient();
        $this->container = static::$kernel->getContainer();
    }

    public function testReadOneEndpointWithAValidModelUuid()
    {
        $uuidObj = Uuid::uuid4();
        $uuid = $uuidObj->toString();

        $elasticDoc = new MockElasticDocument($uuid);
        $mockParamConverter = new MockReadParamConverter($elasticDoc);
        $this->container->set('read_one_param_converter', $mockParamConverter);

        $router = $this->container->get('router');

        $url = $router->generate(
            'read_one_model',
            [
                'uuid' => $uuid
            ]
        );
        $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $responseCode = $response->getStatusCode();
        $this->assertSame(200, $responseCode);

        $responseBody = $response->getContent();

        $handler = new Handler();
        $document = $handler->toDocument($responseBody);

        $classes = $document->getClass();
        $this->assertContains('model', $classes);

        $properties = $document->getProperties();
        $this->assertArrayHasKey('uuid', $properties);
        $this->assertArrayHasKey('name', $properties);
        $this->assertSame($uuid, $properties['uuid']);

        $links = $document->getLinks();
        $this->assertCount(1, $links);

        $selfLink = array_shift($links);
        $this->assertContains('self', $selfLink->getRel());
        $this->assertContains('model', $selfLink->getRel());
    }

    public function testReadOneEndpointWithAnInvalidModelUuid()
    {
        $elasticDoc = new MockElasticDocument();
        $mockParamConverter = new MockReadParamConverter($elasticDoc);
        $this->container->set('read_one_param_converter', $mockParamConverter);

        $router = $this->container->get('router');

        $url = $router->generate(
            'read_one_model',
            [
                'uuid' => 'test'
            ]
        );
        $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $statusCode = $response->getStatusCode();

        $this->assertSame(404, $statusCode);
    }
}
