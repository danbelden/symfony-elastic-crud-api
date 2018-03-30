<?php

namespace Tests\AppBundle\Command;

use Elastica\Client;
use Elastica\Cluster;
use Elastica\Index;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AliasListCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $mockCluster = $this->getMockBuilder(Cluster::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockCluster->expects($this->once())
            ->method('getIndexNames')
            ->willReturn([
                '1',
                '2',
                '3'
            ]);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->once())
            ->method('getCluster')
            ->willReturn($mockCluster);

        $mockClient->expects($this->any())
            ->method('getIndex')
            ->willReturnCallback(function(string $indexName) {
                $numAliases = (int)$indexName;
                $mockAliases = array_fill(0, $numAliases, 'alias');

                $mockIndex = $this->getMockBuilder(Index::class)
                    ->disableOriginalConstructor()
                    ->getMock();

                $mockIndex->expects($this->once())
                    ->method('getAliases')
                    ->willReturn($mockAliases);

                return $mockIndex;
            });

        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->set('elastic_client', $mockClient);

        $application = new Application($kernel);
        $command = $application->find('index:alias:list');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('1 [alias]', $output);
        $this->assertContains('2 [alias, alias]', $output);
        $this->assertContains('3 [alias, alias, alias]', $output);
    }
}
