<?php

namespace Tests\AppBundle\ParamConverter;

use AppBundle\Elastic\Repository as ElasticRepository;
use AppBundle\ParamConverter\Read as ReadParamConverter;
use Elastica\Document as ElasticDocument;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConfig;
use Symfony\Component\HttpFoundation\Request;

class ReadTest extends TestCase
{
    /**
     * @var ReadParamConverter
     */
    private $readParamConverter;

    public function setup()
    {
        $mockElasticRepository = $this->getMockBuilder(ElasticRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['exists', 'fetch'])
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

        $this->readParamConverter = new ReadParamConverter($mockElasticRepository);
    }

    public function testSupports()
    {
        $configuration = new ParamConfig([
            'class' => true
        ]);

        $supports = $this->readParamConverter->supports($configuration);

        $this->assertTrue($supports);
    }

    public function testApplyWithValidRequestUuid()
    {
        $uuid = Uuid::uuid4();
        $uuidString = $uuid->toString();

        $pathParams = ['uuid' => $uuidString];

        $request = new Request([], [], $pathParams);
        $configuration = new ParamConfig([
            'class' => true,
            'name' => 'uuid'
        ]);

        $this->readParamConverter->apply($request, $configuration);

        $elasticDoc = $request->attributes->get('uuid');
        $this->assertInstanceOf(ElasticDocument::class, $elasticDoc);

        $docId = $elasticDoc->getId();
        $this->assertSame($uuidString, $docId);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Document `test` does not exist
     */
    public function testApplyWithInvalidRequestUuid()
    {
        $pathParams = ['uuid' => 'test'];

        $request = new Request([], [], $pathParams);
        $configuration = new ParamConfig([
            'class' => true,
            'name' => 'uuid'
        ]);

        $this->readParamConverter->apply($request, $configuration);
    }
}
