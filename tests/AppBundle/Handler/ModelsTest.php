<?php

namespace Tests\AppBundle\Handler;

use AppBundle\Criteria\Model as ModelCriteria;
use AppBundle\Handler\Models as ModelsHandler;
use Elastica\ResultSet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\AppBundle\Mocks\MockElasticDocument;

class ModelsTest extends KernelTestCase
{
    public function testToDocument()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $router = $container->get('router');
        $modelsHandler = new ModelsHandler($router);

        $criteria = new ModelCriteria();
        $criteria->setName('test')
            ->setLimit(3)
            ->setOffset(2);

        $mockResultSet = $this->getMockBuilder(ResultSet::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDocuments',
                'getTotalHits',
                'getTotalTime',
                'getMaxScore'
            ])
            ->getMock();

        $mockDocumentOne = new MockElasticDocument();
        $mockDocumentTwo = new MockElasticDocument();
        $mockDocumentThree = new MockElasticDocument();

        $mockDocuments = [
            $mockDocumentOne,
            $mockDocumentTwo,
            $mockDocumentThree
        ];

        $mockResultSet->expects($this->once())
            ->method('getDocuments')
            ->willReturn($mockDocuments);

        $mockResultSet->expects($this->any())
            ->method('getTotalHits')
            ->willReturn(10);

        $mockResultSet->expects($this->once())
            ->method('getTotalTime')
            ->willReturn(20);

        $mockResultSet->expects($this->once())
            ->method('getMaxScore')
            ->willReturn(1);

        $sirenDoc = $modelsHandler->toDocument($criteria, $mockResultSet);

        $classes = $sirenDoc->getClass();
        $this->assertContains('models', $classes);
        $this->assertContains('collection', $classes);

        $properties = $sirenDoc->getProperties();
        $this->assertSame('test', $properties['criteria']['name']);
        $this->assertSame(3, $properties['criteria']['limit']);
        $this->assertSame(2, $properties['criteria']['offset']);
        $this->assertSame(10, $properties['totalHits']);
        $this->assertSame(20, $properties['totalTime']);
        $this->assertSame(1, $properties['maxScore']);

        $entities = $sirenDoc->getEntities();
        $this->assertCount(3, $entities);

        foreach ($entities as $entity) {
            $this->assertContains('model', $entity->getClass());
            $this->assertNotEmpty($entity->getProperties());
            $this->assertCount(1, $entity->getLinks());
        }

        $links = $sirenDoc->getLinks();
        $this->assertCount(3, $links);

        $selfLinks = array_filter($links, function ($link) {
            return in_array('self', $link->getRel(), true);
        });
        $selfLink = array_shift($selfLinks);
        $this->assertContains('self', $selfLink->getRel());
        $this->assertContains('models', $selfLink->getRel());
        $this->assertContains('collection', $selfLink->getRel());
        $this->assertNotEmpty($selfLink->getHref());

        $prevLinks = array_filter($links, function ($link) {
            return in_array('prev', $link->getRel(), true);
        });
        $prevLink = array_shift($prevLinks);
        $this->assertContains('prev', $prevLink->getRel());
        $this->assertContains('models', $prevLink->getRel());
        $this->assertContains('collection', $prevLink->getRel());
        $this->assertNotEmpty($prevLink->getHref());

        $nextLinks = array_filter($links, function ($link) {
            return in_array('next', $link->getRel(), true);
        });
        $nextLink = array_shift($nextLinks);
        $this->assertContains('next', $nextLink->getRel());
        $this->assertContains('models', $nextLink->getRel());
        $this->assertContains('collection', $nextLink->getRel());
        $this->assertNotEmpty($nextLink->getHref());
    }
}
