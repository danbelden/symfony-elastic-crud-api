<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Elastic\Repository as ElasticRepository;
use Ramsey\Uuid\Uuid;
use Siren\Handler;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\Mocks\MockElasticDocument;
use Tests\AppBundle\Mocks\MockUpdateParamConverter;

class UpdateControllerTest extends WebTestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Client
     */
    private $client;

    public function setup()
    {
        $this->client = static::createClient();
        $this->container = static::$kernel->getContainer();
    }

    public function testUpdateEndpointWithAValidRequestBody()
    {
        $uuid = Uuid::uuid4();

        $formFactory = $this->container->get('form.factory');
        $modelHandler = $this->container->get('model_handler');

        $mockElasticRepository = $this->getMockBuilder(ElasticRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockUpdateParamConverter = new MockUpdateParamConverter(
            $formFactory,
            $modelHandler,
            $mockElasticRepository
        );

        $mockElasticDocument = new MockElasticDocument($uuid->toString());
        $mockUpdateParamConverter->setDocument($mockElasticDocument);

        $this->container->set('update_param_converter', $mockUpdateParamConverter);

        $router = $this->container->get('router');

        $url = $router->generate(
            'update_model',
            [
                'uuid' => $uuid->toString()
            ]
        );
        $content = json_encode(['name' => 'new']);

        $this->client->request('POST', $url, [], [], [], $content);
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
        $this->assertSame('new', $properties['name']);

        $links = $document->getLinks();
        $this->assertCount(1, $links);

        $selfLink = array_shift($links);
        $this->assertContains('self', $selfLink->getRel());
        $this->assertContains('model', $selfLink->getRel());

        $modelUuid = $properties['uuid'];
        $this->assertContains($modelUuid, $selfLink->getHref());
    }

    public function testUpdateEndpointWithAnInvalidUuid()
    {
        $uuid = Uuid::uuid4();

        $formFactory = $this->container->get('form.factory');
        $modelHandler = $this->container->get('model_handler');

        $mockElasticRepository = $this->getMockBuilder(ElasticRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockUpdateParamConverter = new MockUpdateParamConverter(
            $formFactory,
            $modelHandler,
            $mockElasticRepository
        );

        $this->container->set('update_param_converter', $mockUpdateParamConverter);

        $router = $this->container->get('router');

        $url = $router->generate(
            'update_model',
            [
                'uuid' => 'test'
            ]
        );
        $content = json_encode(['name' => 'new']);

        $this->client->request('POST', $url, [], [], [], $content);

        $response = $this->client->getResponse();
        $statusCode = $response->getStatusCode();

        $this->assertSame(400, $statusCode);
    }

    public function testUpdateEndpointWithAnInvalidRequestBody()
    {
        $router = $this->container->get('router');
        $uuid = Uuid::uuid4();

        $url = $router->generate(
            'update_model',
            [
                'uuid' => $uuid->toString()
            ]
        );
        $content = json_encode(['name' => '']);

        $this->client->request('POST', $url, [], [], [], $content);

        $response = $this->client->getResponse();
        $statusCode = $response->getStatusCode();

        $this->assertSame(400, $statusCode);
    }
}
