<?php

namespace Tests\AppBundle\ParamConverter;

use AppBundle\Elastic\Repository as ElasticRepository;
use AppBundle\ParamConverter\Create as CreateParamConverter;
use Elastica\Document as ElasticDocument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class CreateTest extends KernelTestCase
{
    /**
     * @var CreateParamConverter
     */
    private $createParamConverter;

    public function setup()
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $formFactory = $container->get('form.factory');
        $modelHandler = $container->get('model_handler');

        $mockElasticRepository = $this->getMockBuilder(ElasticRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockElasticRepository->method('add')
            ->willReturn(true);

        $this->createParamConverter = new CreateParamConverter(
            $formFactory,
            $modelHandler,
            $mockElasticRepository
        );
    }

    public function testSupports()
    {
        $configuration = new ParamConfig([
            'class' => true
        ]);

        $supports = $this->createParamConverter->supports($configuration);

        $this->assertTrue($supports);
    }

    public function testApplyWithValidRequestBody()
    {
        $requestBody = ['name' => 'test'];
        $requestBodyJson = json_encode($requestBody);

        $request = new Request([], [], [], [], [], [], $requestBodyJson);
        $configuration = new ParamConfig([
            'class' => 'test',
            'name' => 'testName'
        ]);

        $this->createParamConverter->apply($request, $configuration);

        $elasticDoc = $request->attributes->get('testName');
        $this->assertInstanceOf(ElasticDocument::class, $elasticDoc);

        $docData = $elasticDoc->getData();

        $decodedDocData = json_decode($docData, true);
        $this->assertInternalType('array', $decodedDocData);
        $this->assertArrayHasKey('properties', $decodedDocData);
        $this->assertArrayHasKey('name', $decodedDocData['properties']);
        $this->assertSame($requestBody['name'], $decodedDocData['properties']['name']);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Model `name` must not be blank
     */
    public function testApplyWithInvalidRequestBody()
    {
        $requestBody = ['name' => ''];
        $requestBodyJson = json_encode($requestBody);

        $request = new Request([], [], [], [], [], [], $requestBodyJson);
        $configuration = new ParamConfig([
            'name' => Model::class
        ]);

        $this->createParamConverter->apply($request, $configuration);
    }
}
