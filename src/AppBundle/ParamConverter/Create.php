<?php

namespace AppBundle\ParamConverter;

use AppBundle\Elastic\Repository as ElasticRepo;
use AppBundle\Form\Create as CreateForm;
use AppBundle\Handler\Model as ModelHandler;
use Elastica\Document as ElasticDocument;
use Exception;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Siren\Document as SirenDocument;
use Siren\Handler as SirenHandler;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Create extends Base implements ParamConverterInterface
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var ModelHandler
     */
    protected $modelHandler;

    /**
     * @var ElasticRepo
     */
    protected $elasticRepo;

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param ModelHandler $modelHandler
     * @param ElasticRepo $elasticRepo
     */
    public function __construct(
        FormFactory $formFactory,
        ModelHandler $modelHandler,
        ElasticRepo $elasticRepo
    ) {
        $this->formFactory = $formFactory;
        $this->modelHandler = $modelHandler;
        $this->elasticRepo = $elasticRepo;
    }

    /**
     * Method to convert the request into a Model entity (If valid)
     *
     * @param Request $request
     * @param ParamConverter $configuration
     * @throws BadRequestHttpException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $decodedBody = $this->getDecodedJsonBody($request);

        $form = $this->formFactory->create(
            CreateForm::class,
            null,
            [
                'csrf_protection' => false
            ]
        );
        $form->submit($decodedBody);

        if ($form->isValid() === false) {
            $this->throwFormError($form);
        }

        $formData = $form->getData();
        $sirenDocument = $this->getSirenDocument($formData);

        $elasticDocument = $this->addElasticDocument($sirenDocument);

        $request->attributes->set($configuration->getName(), $elasticDocument);
    }

    /**
     * Method to convert form input data into a siren document
     *
     * @param array $formData
     * @return SirenDocument
     */
    private function getSirenDocument(array $formData)
    {
        $uuid = Uuid::uuid4();
        $uuidString = $uuid->toString();

        $formData['uuid'] = $uuidString;

        return $this->modelHandler->toSirenDocumentFromArray($formData);
    }

    /**
     * Method to convert siren document to elastic document and persist
     *
     * @param SirenDocument $sirenDocument
     * @return ElasticDocument
     * @throws Exception
     */
    private function addElasticDocument(SirenDocument $sirenDocument)
    {
        $sirenHandler = new SirenHandler();
        $sirenJson = $sirenHandler->toJson($sirenDocument);

        $sirenProperties = $sirenDocument->getProperties();
        $docUuid = $sirenProperties['uuid'];

        $elasticDocument = new ElasticDocument();
        $elasticDocument->setId($docUuid);
        $elasticDocument->setData($sirenJson);

        $added = $this->elasticRepo->add($elasticDocument);
        if ($added === false) {
            throw new Exception('Unable to add elastic document via repo!');
        }

        return $elasticDocument;
    }
}
