<?php

namespace AppBundle\Handler;

use InvalidArgumentException;
use Siren\Document;
use Siren\Link;
use Symfony\Component\Routing\Router;

class Model
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
     * Method to convert array data into a siren document for model
     *
     * @param array $data
     * @return Document
     */
    public function toSirenDocumentFromArray(array $data)
    {
        $this->validateSirenDataArray($data);

        $sirenDocument = new Document();
        $sirenDocument->setClass(['model']);
        $sirenDocument->setProperties($data);

        $selfLink = $this->router->generate(
            'read_one_model',
            [
                'uuid' => $data['uuid']
            ],
            $this->router::ABSOLUTE_URL
        );

        $link = new Link();
        $link->setRel(['self', 'model'])
            ->setHref($selfLink);

        $sirenDocument->addLink($link);

        return $sirenDocument;
    }

    /**
     * Helper method to validate the incoming siren data is as required
     *
     * @param array $sirenData
     * @throws InvalidArgumentException
     */
    private function validateSirenDataArray(array $sirenData)
    {
        $requiredProps = ['uuid', 'name'];
        foreach ($requiredProps as $requiredProp) {
            if (empty($sirenData[$requiredProp])) {
                $excMsg = sprintf('Siren property `%s` is missing', $requiredProp);
                throw new InvalidArgumentException($excMsg);
            }
        }
    }
}
