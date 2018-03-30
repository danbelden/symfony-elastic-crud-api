<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Elastic\Repository;
use Elastica\ResultSet;
use Siren\Handler;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Tests\AppBundle\Mocks\MockElasticDocument;

class ReadManyControllerTest extends WebTestCase
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

    public function testReadManyEndpointWithValidParams()
    {
        $mockElasticRepository = $this->getMockBuilder(Repository::class)
            ->setMethods(['search'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockResultSet = $this->getMockBuilder(ResultSet::class)
            ->setMethods([
                'getTotalHits',
                'getTotalTime',
                'getMaxScore',
                'getDocuments'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $mockResultSet->expects($this->any())
            ->method('getTotalHits')
            ->willReturn(3);

        $mockResultSet->expects($this->once())
            ->method('getTotalTime')
            ->willReturn(4);

        $mockResultSet->expects($this->once())
            ->method('getMaxScore')
            ->willReturn(1);

        $mockElasticDocs = [];
        for ($i = 0; $i < 3; $i++) {
            $mockElasticDocs[] = new MockElasticDocument();
        }

        $mockResultSet->expects($this->once())
            ->method('getDocuments')
            ->willReturn($mockElasticDocs);

        $mockElasticRepository->method('search')
            ->willReturn($mockResultSet);

        $this->container->set('elastic_repository', $mockElasticRepository);

        $router = $this->container->get('router');
        $url = $router->generate(
            'read_many_model',
            [
                'name' => 'test',
                'limit' => 3,
                'offset' => 3
            ]
        );

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $responseBody = $response->getContent();

        $responseCode = $response->getStatusCode();
        $this->assertSame(200, $responseCode);

        $handler = new Handler();
        $document = $handler->toDocument($responseBody);

        $classes = $document->getClass();
        $this->assertContains('models', $classes);
        $this->assertContains('collection', $classes);

        $properties = $document->getProperties();
        $this->assertArrayHasKey('criteria', $properties);
        $this->assertArrayHasKey('totalHits', $properties);
        $this->assertArrayHasKey('totalTime', $properties);
        $this->assertArrayHasKey('maxScore', $properties);

        $this->assertSame('test', $properties['criteria']['name']);
        $this->assertSame(3, $properties['criteria']['limit']);
        $this->assertSame(3, $properties['criteria']['offset']);

        $entities = $document->getEntities();
        $this->assertCount(3, $entities);
        foreach ($entities as $entity) {
            $properties = $entity->getProperties();
            $this->assertSame('test', $properties['name']);

            $links = $entity->getLinks();
            $selfLink = array_shift($links);
            $selfLinkRels = $selfLink->getRel();
            $this->assertContains('model', $selfLinkRels);
            $this->assertContains('self', $selfLinkRels);
        }

        $links = $document->getLinks();
        $this->assertCount(2, $links);
        foreach ($links as $link) {
            $rels = $link->getRel();
            $this->assertContains('collection', $rels);
            $this->assertContains('models', $rels);
        }
    }

    public function testReadManyEndpointWithInvalidParams()
    {
        $router = $this->container->get('router');
        $url = $router->generate(
            'read_many_model',
            [
                'test' => 'test'
            ]
        );

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $statusCode = $response->getStatusCode();

        $this->assertSame(400, $statusCode);
    }
}
