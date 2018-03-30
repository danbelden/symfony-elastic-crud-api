<?php

namespace Tests\AppBundle\Command;

use Elastica\Client;
use Elastica\Index;
use Elastica\Response;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AliasMoveCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $mockSourceIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockSourceIndex->expects($this->once())
            ->method('getAliases')
            ->willReturn(['alias']);

        $mockTargetIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockTargetIndex->expects($this->once())
            ->method('getAliases')
            ->willReturn([]);

        $mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse->expects($this->any())
            ->method('isOK')
            ->willReturn(true);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->any())
            ->method('getIndex')
            ->willReturnCallback(function(string $indexName) use ($mockSourceIndex, $mockTargetIndex) {
                return ($indexName === 'source') ?
                    $mockSourceIndex :
                    $mockTargetIndex;
            });

        $mockClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->set('elastic_client', $mockClient);

        $application = new Application($kernel);
        $command = $application->find('index:alias:move');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'source-index' => 'source',
            'target-index' => 'target',
            'alias' => 'alias'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains(
            'Alias "alias" was moved from source index "source" to target index "target"!',
            $output
        );
    }
}
