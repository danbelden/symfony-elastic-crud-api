<?php

namespace Tests\AppBundle\Command;

use Elastica\Client;
use Elastica\Index;
use Elastica\Type;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TypeInfoCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $mockType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockType->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $mockType->expects($this->once())
            ->method('getMapping')
            ->willReturn(['test' => 'test']);

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
        $command = $application->find('index:type:info');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'index' => 'index',
            'type' => 'type'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('{"test":"test"}', $output);
    }
}
