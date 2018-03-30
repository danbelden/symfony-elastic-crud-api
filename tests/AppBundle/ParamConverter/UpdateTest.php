<?php

namespace Tests\AppBundle\ParamConverter;

use AppBundle\Elastic\Repository as ElasticRepository;
use AppBundle\ParamConverter\Update as UpdateParamConverter;
use Elastica\Document as ElasticDocument;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConfig;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class UpdateTest extends KernelTestCase
{
    /**
     * @var UpdateParamConverter
     */
    private $updateParamConverter;

    public function setup()
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $formFactory = $container->get('form.factory');
        $modelHandler = $container->get('model_handler');

        $mockElasticRepository = $this->getMockBuilder(ElasticRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockElasticRepository->expects($this->any())
            ->method('exists')
            ->willReturnCallback(function(string $uuid) {
                return strlen($uuid) === 36;
            });

        $mockElasticRepository->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(function(string $uuid) {
                return new ElasticDocument($uuid);
            });

        $mockElasticRepository->expects($this->any())
            ->method('update')
            ->willReturn(true);

        $this->updateParamConverter = new UpdateParamConverter(
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

        $supports = $this->updateParamConverter->supports($configuration);

        $this->assertTrue($supports);
    }

    public function testApplyWithValidUuidAndRequestBody()
    {
        $uuid = Uuid::uuid4();
        $uuidString = $uuid->toString();

        $pathParams = ['uuid' => $uuidString];
        $requestBody = ['name' => 'update'];
        $requestBodyJson = json_encode($requestBody);

        $request = new Request([], [], $pathParams, [], [], [], $requestBodyJson);
        $configuration = new ParamConfig([
            'class' => true,
            'name' => 'test'
        ]);

        $this->updateParamConverter->apply($request, $configuration);

        $elasticDoc = $request->attributes->get('test');
        $this->assertInstanceOf(ElasticDocument::class, $elasticDoc);

        $docId = $elasticDoc->getId();
        $this->assertSame($uuidString, $docId);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Model `name` must not be blank
     */
    public function testApplyWithValidUuidAndInvalidRequestBody()
    {
        $uuid = Uuid::uuid4();

        $pathParams = ['uuid' => $uuid->toString()];
        $requestBody = ['name' => ''];
        $requestBodyJson = json_encode($requestBody);

        $request = new Request([], [], $pathParams, [], [], [], $requestBodyJson);
        $configuration = new ParamConfig([
            'class' => true
        ]);

        $this->updateParamConverter->apply($request, $configuration);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Document `test-false` does not exist
     */
    public function testApplyWithInvalidUuid()
    {
        $pathParams = ['uuid' => 'test-false'];
        $requestBody = ['name' => 'test'];
        $requestBodyJson = json_encode($requestBody);

        $request = new Request([], [], $pathParams, [], [], [], $requestBodyJson);
        $configuration = new ParamConfig([
            'class' => true
        ]);

        $this->updateParamConverter->apply($request, $configuration);
    }
}
