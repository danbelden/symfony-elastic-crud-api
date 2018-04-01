<?php

namespace Tests\AppBundle\Elastic;

use AppBundle\Criteria\Model as ModelCriteria;
use AppBundle\Elastic\QueryBuilder;
use Elastica\Query;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function testBuild()
    {
        $criteria = new ModelCriteria();
        $criteria->setName('test')
                ->setLimit(20)
                ->setOffset(10)
                ->setOrderField('test')
                ->setOrderDirection('DESC');

        $queryBuilder = new QueryBuilder();
        $query = $queryBuilder->build($criteria);

        assert($query instanceof Query);
        $queryArray = $query->toArray();
        $this->assertArrayHasKey('query', $queryArray);
        $this->assertArrayHasKey('match', $queryArray['query']);
        $this->assertArrayHasKey('properties.name', $queryArray['query']['match']);
        $this->assertArrayHasKey('size', $queryArray);
        $this->assertArrayHasKey('from', $queryArray);
        $this->assertSame($queryArray['query']['match']['properties.name'], 'test');
        $this->assertSame($queryArray['size'], 20);
        $this->assertSame($queryArray['from'], 10);

        $sort = $query->getParam('sort');
        $this->assertArrayHasKey('test', $sort);
        $this->assertArrayHasKey('order', $sort['test']);
        $this->assertSame($sort['test']['order'], 'DESC');
    }
}
