<?php

namespace Tests\AppBundle\Command;

use Elastica\Client;
use Elastica\Index;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TypeListCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->once())
            ->method('getMapping')
            ->willReturn([
                'test1' => null,
                'test2' => null,
                'test3' => null
            ]);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->once())
            ->method('getIndex')
            ->with('index')
            ->willReturn($mockIndex);

        $container->set('elastic_client', $mockClient);

        $application = new Application($kernel);
        $command = $application->find('index:type:list');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'index' => 'index'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('test1', $output);
        $this->assertContains('test2', $output);
        $this->assertContains('test3', $output);
    }
}
