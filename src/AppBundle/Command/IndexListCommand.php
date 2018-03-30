<?php

namespace AppBundle\Command;

use Elastica\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexListCommand extends ContainerAwareCommand
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('index:list')
            ->setDescription('Lists the indexes in elasticsearch');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cluster = $this->client->getCluster();

        $indexNames = $cluster->getIndexNames();
        if (empty($indexNames)) {
            $output->writeln('There are no indexes in the cluster!');

            return;
        }

        foreach ($indexNames as $indexName) {
            $output->writeln($indexName);
        }
    }
}
