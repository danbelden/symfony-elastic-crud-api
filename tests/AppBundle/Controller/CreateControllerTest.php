<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Elastic\Repository as ElasticRepository;
use Siren\Handler;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateControllerTest extends WebTestCase
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

        $mockElasticRepository = $this->getMockBuilder(ElasticRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add'])
            ->getMock();

        $mockElasticRepository->expects($this->any())
            ->method('add')
            ->willReturn(true);

        $this->container->set('elastic_repository', $mockElasticRepository);
    }

    public function testCreateEndpointWithAValidRequestBody()
    {
        $router = $this->container->get('router');
        $url = $router->generate('create_model');

        $content = json_encode(['name' => 'test']);
        $this->client->request('POST', $url, [], [], [], $content);

        $response = $this->client->getResponse();

        $responseCode = $response->getStatusCode();
        $this->assertSame(201, $responseCode);

        $responseBody = $response->getContent();

        $handler = new Handler();
        $document = $handler->toDocument($responseBody);

        $classes = $document->getClass();
        $this->assertContains('model', $classes);

        $properties = $document->getProperties();
        $this->assertArrayHasKey('uuid', $properties);
        $this->assertArrayHasKey('name', $properties);
        $this->assertSame('test', $properties['name']);

        $links = $document->getLinks();
        $this->assertCount(1, $links);

        $selfLink = array_shift($links);
        $this->assertContains('self', $selfLink->getRel());
        $this->assertContains('model', $selfLink->getRel());

        $modelUuid = $properties['uuid'];
        $this->assertContains($modelUuid, $selfLink->getHref());
    }

    public function testCreateEndpointWithAnInvalidRequestBody()
    {
        $router = $this->container->get('router');
        $url = $router->generate('create_model');

        $content = json_encode(['name' => '']);
        $this->client->request('POST', $url, [], [], [], $content);

        $response = $this->client->getResponse();
        $statusCode = $response->getStatusCode();

        $this->assertSame(400, $statusCode);
    }
}
