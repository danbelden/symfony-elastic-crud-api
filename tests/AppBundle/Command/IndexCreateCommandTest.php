<?php

namespace Tests\AppBundle\Command;

use DateTime;
use Elastica\Client;
use Elastica\Index;
use Elastica\Response;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class IndexCreateCommandTest extends KernelTestCase
{
    public function testExecuteWithNoIndexArgument()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse->expects($this->any())
            ->method('isOK')
            ->willReturn(true);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $mockIndex->expects($this->once())
            ->method('create')
            ->willReturn($mockResponse);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $curDate = new DateTime();
        $elasticType = $container->getParameter('elastic_type');
        $indexName = $elasticType . '_' . $curDate->format('Y_m_d');

        $mockClient->expects($this->any())
            ->method('getIndex')
            ->with($indexName)
            ->willReturn($mockIndex);

        $container->set('elastic_client', $mockClient);

        $application = new Application($kernel);
        $command = $application->find('index:create');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);

        $output = $commandTester->getDisplay();

        $successMsg = sprintf('Index "%s" created sucessfully!', $indexName);
        $this->assertContains($successMsg, $output);
    }

    public function testExecuteWithIndexArgument()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse->expects($this->any())
            ->method('isOK')
            ->willReturn(true);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $mockIndex->expects($this->once())
            ->method('create')
            ->willReturn($mockResponse);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->any())
            ->method('getIndex')
            ->with('test')
            ->willReturn($mockIndex);

        $container->set('elastic_client', $mockClient);

        $application = new Application($kernel);
        $command = $application->find('index:create');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'index' => 'test'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Index "test" created sucessfully!', $output);
    }
}
