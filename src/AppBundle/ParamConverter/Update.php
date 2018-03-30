<?php

namespace AppBundle\ParamConverter;

use AppBundle\Form\Update as UpdateForm;
use Elastica\Document as ElasticDocument;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Update extends Create implements ParamConverterInterface
{
    /**
     * Method to convert the request into a Model entity (If valid)
     *
     * @param Request $request
     * @param ParamConverter $configuration
     * @throws BadRequestHttpException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $form = $this->formFactory->create(
            UpdateForm::class,
            null,
            [
                'csrf_protection' => false
            ]
        );

        // [Hack] Remove form fields that are null to enable partial updates
        $decodedBody = $this->getDecodedJsonBody($request);
        foreach ($form->getIterator() as $field) {
            if (!isset($decodedBody[$field->getName()])) {
                $form->remove($field->getName());
            }
        }

        $form->submit($decodedBody);

        if ($form->isValid() === false) {
            $this->throwFormError($form);
        }

        $uuid = $request->get('uuid');
        if (empty($uuid)) {
            throw new BadRequestHttpException('Parameter `uuid` is required for update');
        }

        if (empty($decodedBody)) {
            throw new BadRequestHttpException('No update parameters provided');
        }

        $elasticDocument = $this->getDocument($uuid);
        $this->updatedDocumentWithRequestBody($elasticDocument, $decodedBody);

        $updated = $this->updateDocumentInStorage($elasticDocument);
        if ($updated === false) {
            throw new RuntimeException('Unable to update elastic document');
        }

        return $request->attributes->set($configuration->getName(), $elasticDocument);
    }

    /**
     * Helper function to check exists and then retrieve a document by uuid
     *
     * @param string $uuid
     * @return ElasticDocument
     * @throws BadRequestHttpException
     */
    protected function getDocument(string $uuid): ElasticDocument
    {
        $exists = $this->elasticRepo->exists($uuid);
        if ($exists === false) {
            $excMsg = sprintf('Document `%s` does not exist', $uuid);
            throw new BadRequestHttpException($excMsg);
        }

        return $this->elasticRepo->fetch($uuid);
    }

    /**
     * Helper method to retrieve and map updates onto an elastic document
     *
     * @param ElasticDocument $elasticDocument
     * @param array $decodedBody
     */
    protected function updatedDocumentWithRequestBody(
        ElasticDocument &$elasticDocument,
        array $decodedBody
    ) {
        $documentData = $elasticDocument->getData();

        if (isset($documentData['properties']['name']) && !empty($decodedBody['name'])) {
            $documentData['properties']['name'] = $decodedBody['name'];
        }

        $elasticDocument->setData($documentData);
    }

    /**
     * Helper method to update the elastic search instance with a given document
     *
     * @param ElasticDocument $elasticDocument
     * @return bool
     */
    protected function updateDocumentInStorage(ElasticDocument $elasticDocument): bool
    {
        return $this->elasticRepo->update($elasticDocument);
    }
}
