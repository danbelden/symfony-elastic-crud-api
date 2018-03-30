<?php

namespace Tests\AppBundle\Command;

use Elastica\Client;
use Elastica\Index;
use Elastica\Response;
use Elastica\Type;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TypeCreateCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    private $tmpPath;

    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse->expects($this->once())
            ->method('isOK')
            ->willReturn(true);

        $mockType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockType->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $mockType->expects($this->once())
            ->method('setMapping')
            ->with([])
            ->willReturn($mockResponse);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->once())
            ->method('getType')
            ->with('type')
            ->willReturn($mockType);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->once())
            ->method('getIndex')
            ->with('index')
            ->willReturn($mockIndex);

        $container->set('elastic_client', $mockClient);

        $application = new Application($kernel);
        $command = $application->find('index:type:create');

        $this->createTempMappingFile();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'index' => 'index',
            'type' => 'type',
            'path' => $this->tmpPath
        ]);

        $this->removeTempMappingFile();

        $output = $commandTester->getDisplay();
        $this->assertContains('Type "index/type" created sucessfully!', $output);
    }

    private function createTempMappingFile()
    {
        $tmpFileDir = sys_get_temp_dir();
        $this->tmpPath = $tmpFileDir . DIRECTORY_SEPARATOR . time() . '.json';

        $created = file_put_contents($this->tmpPath, '{}');
        if ($created === false) {
            throw new \RuntimeException('Could not create file ' . $this->tmpPath);
        }
    }

    private function removeTempMappingFile()
    {
        if (is_readable($this->tmpPath) === false) {
            throw new \RuntimeException('Could not read file ' . $this->tmpPath);
        }

        $deleted = unlink($this->tmpPath);
        if ($deleted === false) {
            throw new \RuntimeException('Could not delete file ' . $this->tmpPath);
        }
    }
}
