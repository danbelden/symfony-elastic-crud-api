<?php

namespace Tests\AppBundle\Command;

use Elastica\Client;
use Elastica\Index;
use Elastica\Response;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AliasAddCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse->expects($this->once())
            ->method('isOK')
            ->willReturn(true);

        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockIndex->expects($this->once())
            ->method('addAlias')
            ->willReturn($mockResponse);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->once())
            ->method('getIndex')
            ->willReturn($mockIndex);

        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->set('elastic_client', $mockClient);

        $application = new Application($kernel);
        $command = $application->find('index:alias:add');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            'index' => 'index',
            'alias' => 'alias'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains(
            'Alias "alias" was added to index "index" sucessfully!',
            $output
        );
    }
}
