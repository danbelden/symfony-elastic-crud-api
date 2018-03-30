<?php

namespace Tests\AppBundle\Elastic;

use AppBundle\Elastic\Repository;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    public function testExists()
    {
        $mockDocument = $this->getMockBuilder(Document::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDocument->expects($this->once())
            ->method('getId')
            ->willReturn('test');

        $mockType = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocument'])
            ->getMock();

        $mockType->expects($this->once())
            ->method('getDocument')
            ->with('test')
            ->willReturn($mockDocument);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->once())
            ->method('getType')
            ->with('type')
            ->willReturn($mockType);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->once())
            ->method('getIndex')
            ->with('index')
            ->willReturn($mockIndex);

        $indexName = 'index';
        $typeName = 'type';

        $repository = new Repository($mockClient, $indexName, $typeName);
        $exists = $repository->exists('test');
        $this->assertTrue($exists);
    }

    public function testFetch()
    {
        $mockDocument = $this->getMockBuilder(Document::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDocument->expects($this->once())
            ->method('getId')
            ->willReturn('test');

        $mockType = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocument'])
            ->getMock();

        $mockType->expects($this->once())
            ->method('getDocument')
            ->with('test')
            ->willReturn($mockDocument);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->once())
            ->method('getType')
            ->with('type')
            ->willReturn($mockType);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->once())
            ->method('getIndex')
            ->with('index')
            ->willReturn($mockIndex);

        $indexName = 'index';
        $typeName = 'type';

        $repository = new Repository($mockClient, $indexName, $typeName);
        $document = $repository->fetch('test');
        $this->assertSame('test', $document->getId());
    }

    public function testAdd()
    {
        $mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse->expects($this->once())
            ->method('isOK')
            ->willReturn(true);

        $mockDocument = $this->getMockBuilder(Document::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDocument->expects($this->any())
            ->method('getId')
            ->willReturn('test');

        $mockType = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocument', 'addDocument'])
            ->getMock();

        $mockType->expects($this->once())
            ->method('getDocument')
            ->with('test')
            ->willReturnCallback(function($id) {
                $excMsg = 'doc id ' . $id . ' not found';
                throw new NotFoundException($excMsg);
            });

        $mockType->expects($this->once())
            ->method('addDocument')
            ->with($mockDocument)
            ->willReturn($mockResponse);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->any())
            ->method('getType')
            ->with('type')
            ->willReturn($mockType);

        $mockIndex->expects($this->once())
            ->method('refresh')
            ->willReturn(true);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->any())
            ->method('getIndex')
            ->with('index')
            ->willReturn($mockIndex);

        $indexName = 'index';
        $typeName = 'type';

        $repository = new Repository($mockClient, $indexName, $typeName);
        $added = $repository->add($mockDocument, true);
        $this->assertTrue($added);
    }

    public function testUpdate()
    {
        $mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse->expects($this->once())
            ->method('isOK')
            ->willReturn(true);

        $mockDocument = $this->getMockBuilder(Document::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDocument->expects($this->any())
            ->method('getId')
            ->willReturn('test');

        $mockType = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocument', 'addDocument'])
            ->getMock();

        $mockType->expects($this->once())
            ->method('getDocument')
            ->with('test')
            ->willReturn($mockDocument);

        $mockType->expects($this->once())
            ->method('addDocument')
            ->with($mockDocument)
            ->willReturn($mockResponse);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->any())
            ->method('getType')
            ->with('type')
            ->willReturn($mockType);

        $mockIndex->expects($this->once())
            ->method('refresh')
            ->willReturn(true);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->any())
            ->method('getIndex')
            ->with('index')
            ->willReturn($mockIndex);

        $indexName = 'index';
        $typeName = 'type';

        $repository = new Repository($mockClient, $indexName, $typeName);
        $updated = $repository->update($mockDocument, true);
        $this->assertTrue($updated);
    }

    public function testDelete()
    {
        $mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse->expects($this->once())
            ->method('isOK')
            ->willReturn(true);

        $mockDocument = $this->getMockBuilder(Document::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDocument->expects($this->any())
            ->method('getId')
            ->willReturn('test');

        $mockType = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocument', 'deleteById'])
            ->getMock();

        $mockType->expects($this->once())
            ->method('getDocument')
            ->with('test')
            ->willReturn($mockDocument);

        $mockType->expects($this->once())
            ->method('deleteById')
            ->with('test')
            ->willReturn($mockResponse);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->any())
            ->method('getType')
            ->with('type')
            ->willReturn($mockType);

        $mockIndex->expects($this->once())
            ->method('refresh')
            ->willReturn(true);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->any())
            ->method('getIndex')
            ->with('index')
            ->willReturn($mockIndex);

        $indexName = 'index';
        $typeName = 'type';

        $repository = new Repository($mockClient, $indexName, $typeName);
        $deleted = $repository->delete($mockDocument, true);
        $this->assertTrue($deleted);
    }

    public function testSearch()
    {
        $query = new Query();

        $mockResultSet = $this->getMockBuilder(ResultSet::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockType = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->setMethods(['search'])
            ->getMock();

        $mockType->expects($this->once())
            ->method('search')
            ->with($query)
            ->willReturn($mockResultSet);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->any())
            ->method('getType')
            ->with('type')
            ->willReturn($mockType);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->any())
            ->method('getIndex')
            ->with('index')
            ->willReturn($mockIndex);

        $indexName = 'index';
        $typeName = 'type';

        $repository = new Repository($mockClient, $indexName, $typeName);
        $resultSet = $repository->search($query);
        $this->assertSame($resultSet, $mockResultSet);
    }
}
