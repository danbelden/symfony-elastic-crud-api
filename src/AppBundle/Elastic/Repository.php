<?php

namespace AppBundle\Elastic;

use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Query;
use Elastica\ResultSet;
use Symfony\Bridge\Monolog\Logger;

class Repository
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $index;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Client $client
     * @param string $index
     * @param string $type
     * @param Logger $logger
     */
    public function __construct(
        Client $client,
        string $index,
        string $type,
        Logger $logger = null
    ) {
        $this->client = $client;
        $this->index = $index;
        $this->type = $type;
        $this->logger = $logger;
    }

    /**
     * Method to check if a given document exists with the given uuid
     *
     * @param string $uuid
     * @return bool
     */
    public function exists(string $uuid): bool
    {
        $index = $this->client->getIndex($this->index);
        $type = $index->getType($this->type);

        try {
            $document = $type->getDocument($uuid);

            return $document->getId() === $uuid;
        } catch (NotFoundException $exc) {
            return false;
        }
    }

    /**
     * Method to fetch a document by uuid or id
     *
     * @param string $uuid
     * @return Document
     */
    public function fetch(string $uuid): Document
    {
        $index = $this->client->getIndex($this->index);
        $type = $index->getType($this->type);

        $elasticDocument = $type->getDocument($uuid);

        return $elasticDocument;
    }

    /**
     * Method to add a given document to the document store
     *
     * @param Document $model
     * @param bool $refresh
     * @return bool
     */
    public function add(Document $document, bool $refresh = false): bool
    {
        $uuid = $document->getId();
        if (!empty($uuid) && $this->exists($uuid) === true) {
            if ($this->hasLogger()) {
                $wrnMsg = sprintf('Document `%s` already exists cant create', $uuid);
                $this->logger->addWarning($wrnMsg);
            }

            return false;
        }

        $index = $this->client->getIndex($this->index);
        $type = $index->getType($this->type);

        $response = $type->addDocument($document);
        if ($response->isOk() === false) {
            if ($this->hasLogger()) {
                $this->logger->addCritical($response->getErrorMessage());
            }

            return false;
        }

        if ($refresh) {
            $index->refresh();
        }

        return true;
    }

    /**
     * Method to update a given document in the document store
     *
     * @param Document $model
     * @param bool $refresh
     * @return bool
     */
    public function update(Document $document, bool $refresh = false): bool
    {
        $uuid = $document->getId();
        if (empty($uuid)) {
            if ($this->hasLogger()) {
                $wrnMsg = 'Document needs an id to be updated';
                $this->logger->addWarning($wrnMsg);
            }

            return false;
        }

        if ($this->exists($uuid) === false) {
            if ($this->hasLogger()) {
                $wrnMsg = sprintf('Document `%s` does not exist for update', $uuid);
                $this->logger->addWarning($wrnMsg);
            }

            return false;
        }

        $index = $this->client->getIndex($this->index);
        $type = $index->getType($this->type);

        $response = $type->addDocument($document);
        if ($response->isOk() === false) {
            if ($this->hasLogger()) {
                $this->logger->addCritical($response->getErrorMessage());
            }

            return false;
        }

        if ($refresh) {
            $index->refresh();
        }

        return true;
    }

    /**
     * Method to remove a given document from the document store
     *
     * @param Document $document
     * @param bool $refresh
     * @return bool
     */
    public function delete(Document $document, bool $refresh = false): bool
    {
        $uuid = $document->getId();
        if (empty($uuid)) {
            if ($this->hasLogger()) {
                $wrnMsg = 'Document needs an id to be deleted';
                $this->logger->addWarning($wrnMsg);
            }

            return false;
        }

        if ($this->exists($uuid) === false) {
            if ($this->hasLogger()) {
                $wrnMsg = sprintf('Document `%s` does not exist for delete', $uuid);
                $this->logger->addWarning($wrnMsg);
            }

            return false;
        }

        $index = $this->client->getIndex($this->index);
        $type = $index->getType($this->type);

        $response = $type->deleteById($uuid);
        if ($response->isOk() === false) {
            if ($this->hasLogger()) {
                $this->logger->addCritical($response->getErrorMessage());
            }

            return false;
        }

        if ($refresh) {
            $index->refresh();
        }

        return true;
    }

    /**
     * Method to find documents by elastica query
     *
     * @param Query $query
     * @return ResultSet
     */
    public function search(Query $query): ResultSet
    {
        $index = $this->client->getIndex($this->index);
        $type = $index->getType($this->type);

        return $type->search($query);
    }

    /**
     * Helper method to check if there is a registered logger
     *
     * @return bool
     */
    private function hasLogger(): bool
    {
        return $this->logger !== null;
    }
}
