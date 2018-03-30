<?php

namespace Tests\AppBundle\Command;

use Elastica\Client;
use Elastica\Cluster;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class IndexListCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $mockCluster = $this->getMockBuilder(Cluster::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockCluster->expects($this->once())
            ->method('getIndexNames')
            ->willReturn([
                'test1',
                'test2',
                'test3'
            ]);

        $mockClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->any())
            ->method('getCluster')
            ->willReturn($mockCluster);

        $container->set('elastic_client', $mockClient);

        $application = new Application($kernel);
        $command = $application->find('index:list');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('test1', $output);
        $this->assertContains('test2', $output);
        $this->assertContains('test3', $output);
    }
}
