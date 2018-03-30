<?php

namespace AppBundle\Handler;

use AppBundle\Criteria\Model as ModelCriteria;
use AppBundle\Paginator\LimitOffsetHelper;
use Elastica\Document as ElasticDocument;
use Elastica\ResultSet;
use Siren\Document;
use Siren\Entity;
use Siren\Link;
use Symfony\Component\Routing\Router;

class Models
{
    /**
     * @var Router
     */
    private $router;

    /**
     * Constructor
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Method to convert a result set to siren document ready for output
     *
     * @param ModelCriteria $criteria
     * @param ResultSet $resultSet
     * @return Document
     */
    public function toDocument(ModelCriteria $criteria, ResultSet $resultSet)
    {
        $document = new Document();
        $document->setClass(['models', 'collection']);

        $properties = $this->getProperties($criteria, $resultSet);
        $document->setProperties($properties);

        $entities = $this->getEntities($resultSet);
        $document->setEntities($entities);

        $links = $this->getLinks($criteria, $resultSet);
        $document->setLinks($links);

        return $document;
    }

    /**
     * Helper method to convert criteria and result set into a properties array
     *
     * @param ModelCriteria $criteria
     * @param ResultSet $resultSet
     * @return array
     */
    private function getProperties(ModelCriteria $criteria, ResultSet $resultSet)
    {
        return [
            'criteria' => $criteria->toArray(),
            'totalHits' => $resultSet->getTotalHits(),
            'totalTime' => $resultSet->getTotalTime(),
            'maxScore' => $resultSet->getMaxScore(),
        ];
    }

    /**
     * Helper method to convert a result set object into an entities array
     *
     * @param ResultSet $resultSet
     * @return Entity[]
     */
    private function getEntities(ResultSet $resultSet)
    {
        $elasticDocuments = $resultSet->getDocuments();

        $entities = [];
        foreach ($elasticDocuments as $elasticDocument) {
            assert($elasticDocument instanceof ElasticDocument);
            $entities[] = $this->getEntity($elasticDocument);
        }

        return $entities;
    }

    /**
     * Helper method to create a siren entity object from an elastic document object
     *
     * @param ElasticDocument $elasticDocument
     * @return Entity
     */
    private function getEntity(ElasticDocument $elasticDocument)
    {
        $docData = $elasticDocument->getData();

        $entity = new Entity();

        if (isset($docData['class'])) {
            $entity->setClass($docData['class']);
        }

        if (isset($docData['properties'])) {
            $entity->setProperties($docData['properties']);
        }

        if (isset($docData['links'])) {
            foreach ($docData['links'] as $linkData) {
                $link = new Link();

                if (isset($linkData['href'])) {
                    $link->setHref($linkData['href']);
                }

                if (isset($linkData['rel'])) {
                    $link->setRel($linkData['rel']);
                }

                $entity->addLink($link);
            }
        }

        return $entity;
    }

    /**
     * Helper method to build the links array from the criteria and match count
     *
     * @param ModelCriteria $criteria
     * @param ResultSet $resultSet
     * @return Link[]
     */
    private function getLinks(ModelCriteria $criteria, ResultSet $resultSet)
    {
        $links = [];

        $links[] = $this->getSelfLink($criteria);

        $prevLink = $this->getPrevLink($criteria, $resultSet->getTotalHits());
        if ($prevLink instanceof Link) {
            $links[] = $prevLink;
        }

        $nextLink = $this->getNextLink($criteria, $resultSet->getTotalHits());
        if ($nextLink instanceof Link) {
            $links[] = $nextLink;
        }

        return $links;
    }

    /**
     * Helper method to generate a self referencing link to this page of the
     * models collection. Converts the criteria object into a url effectively.
     *
     * @param ModelCriteria $criteria
     * @return string
     */
    private function getSelfLink(ModelCriteria $criteria)
    {
        $params = $criteria->toArray();

        $linkHref = $this->router->generate(
            'read_many_model',
            $params,
            Router::ABSOLUTE_URL
        );

        $link = new Link();
        $link->setHref($linkHref);
        $link->setRel(['self', 'models', 'collection']);

        return $link;
    }

    /**
     * Helper method to create a link to the previous result set (pagination)
     *
     * @param ModelCriteria $criteria
     * @param int $count
     * @return Link
     */
    private function getPrevLink(ModelCriteria $criteria, $count)
    {
        $limit = $criteria->getLimitOrDefault();
        $offset = $criteria->getOffsetOrDefault();

        $limOffHelper = new LimitOffsetHelper();
        $prevLimit = $limOffHelper->getPreviousLimit($limit, $offset, $count);
        $prevOffset = $limOffHelper->getPreviousOffset($limit, $offset, $count);
        if ($prevLimit === null || $prevOffset === null) {
            return null;
        }

        $params = $criteria->toArray();
        $params['limit'] = $prevLimit;
        $params['offset'] = $prevOffset;

        $linkHref = $this->router->generate(
            'read_many_model',
            $params,
            Router::ABSOLUTE_URL
        );

        $link = new Link();
        $link->setHref($linkHref);
        $link->setRel(['prev', 'models', 'collection']);

        return $link;
    }

    /**
     * Helper method to create a link to the following result set (pagination)
     *
     * @param ModelCriteria $criteria
     * @param int $count
     * @return Link
     */
    private function getNextLink(ModelCriteria $criteria, $count)
    {
        $limit = $criteria->getLimitOrDefault();
        $offset = $criteria->getOffsetOrDefault();

        $limOffHelper = new LimitOffsetHelper();
        $nextLimit = $limOffHelper->getNextLimit($limit, $offset, $count);
        $nextOffset = $limOffHelper->getNextOffset($limit, $offset, $count);
        if ($nextLimit === null || $nextOffset === null) {
            return null;
        }

        $params = $criteria->toArray();
        $params['limit'] = $nextLimit;
        $params['offset'] = $nextOffset;

        $linkHref = $this->router->generate(
            'read_many_model',
            $params,
            Router::ABSOLUTE_URL
        );

        $link = new Link();
        $link->setHref($linkHref);
        $link->setRel(['next', 'models', 'collection']);

        return $link;
    }
}
