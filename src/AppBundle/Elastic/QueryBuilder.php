<?php

namespace AppBundle\Elastic;

use AppBundle\Criteria\Model as ModelCriteria;
use Elastica\Query;
use Elastica\Query\Match as MatchQuery;

class QueryBuilder
{
    /**
     * Method to build an elastica query using a criteria object
     *
     * @param ModelCriteria $criteria
     * @return Query
     */
    public function build(ModelCriteria $criteria): Query
    {
        $query = new Query();

        $this->appendName($criteria, $query)
            ->appendOrder($criteria, $query);

        $limit = $criteria->getLimitOrDefault();
        $query->setSize($limit);

        $offset = $criteria->getOffsetOrDefault();
        $query->setFrom($offset);

        return $query;
    }

    /**
     * Helper method to append the name parameter if passed
     *
     * @param ModelCriteria $criteria
     * @param Query $query
     * @return $this
     */
    private function appendName(ModelCriteria $criteria, Query $query)
    {
        $name = $criteria->getName();
        if (empty($name)) {
            return $this;
        }

        $matchQuery = new MatchQuery();
        $matchQuery->setField('properties.name', $name);

        $query->setQuery($matchQuery);

        return $this;
    }

    /**
     * Helper method to append the sort parameters if passed
     *
     * @param ModelCriteria $criteria
     * @param Query $query
     * @return $this
     */
    private function appendOrder(ModelCriteria $criteria, Query $query)
    {
        $orderField = $criteria->getOrderField();
        if (empty($orderField)) {
            return $this;
        }

        $orderDirection = $criteria->getOrderDirectionOrDefault();
        $query->setSort([
            $orderField => [
                'order' => $orderDirection
            ]
        ]);

        return $this;
    }
}
