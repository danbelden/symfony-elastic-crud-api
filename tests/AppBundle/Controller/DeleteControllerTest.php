<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Elastic\Repository;
use Elastica\Document as ElasticDocument;
use Ramsey\Uuid\Uuid;
use Siren\Handler;
use Siren\Link;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class DeleteControllerTest extends WebTestCase
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

        $mockElasticRepository = $this->getMockBuilder(Repository::class)
            ->setMethods(['exists', 'fetch', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockElasticRepository->expects($this->any())
            ->method('exists')
            ->willReturnCallback(function(string $uuid) {
                return strlen($uuid) === 36;
            });

        $mockElasticRepository->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(function(string $uuid) {
                $router = $this->container->get('router');
                $mockDocument = new ElasticDocument();
                $mockDocument->setData([
                    'class' => ['model', 'self'],
                    'properties' => [
                        'uuid' => $uuid,
                        'name' => 'test'
                    ],
                    'links' => [
                        [
                            'rel' => ['self', 'model'],
                            'href' => $router->generate(
                                'read_many_model',
                                [
                                    'uuid' => $uuid
                                ]
                            )
                        ]
                    ]
                ]);

                return $mockDocument;
            });

        $mockElasticRepository->expects($this->any())
            ->method('delete')
            ->willReturn(true);

        $this->container->set('elastic_repository', $mockElasticRepository);
    }

    public function testDeleteEndpointWithAValidModelUuid()
    {
        $uuid = Uuid::uuid4();

        $router = $this->container->get('router');
        $url = $router->generate('delete_model', ['uuid' => $uuid->toString()]);

        $this->client->request('DELETE', $url);

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
        $this->assertSame($uuid->toString(), $properties['uuid']);

        $links = $document->getLinks();
        $this->assertCount(1, $links);

        $selfLink = array_shift($links);
        $this->assertInstanceOf(Link::class, $selfLink);
        $this->assertContains('self', $selfLink->getRel());
        $this->assertContains('model', $selfLink->getRel());

        $this->assertContains($uuid->toString(), $selfLink->getHref());
    }

    public function testDeleteEndpointWithAnInvalidModelUuid()
    {
        $router = $this->container->get('router');
        $url = $router->generate('delete_model', ['uuid' => 'test']);

        $this->client->request('DELETE', $url);

        $response = $this->client->getResponse();
        $statusCode = $response->getStatusCode();

        $this->assertSame(400, $statusCode);
    }
}
