<?php

namespace AppBundle\Command;

use Elastica\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexDeleteCommand extends ContainerAwareCommand
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
        $this->setName('index:delete')
            ->setDescription('Deletes an index with a given name')
            ->addArgument(
                'index',
                InputArgument::REQUIRED,
                'The index name'
            );
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexName = $input->getArgument('index');
        $index = $this->client->getIndex($indexName);

        if ($index->exists() === false) {
            $msg = sprintf('[Error] Index "%s" does not exist!', $indexName);
            $output->writeln($msg);

            return;
        }

        $response = $index->delete();

        if ($response->isOk()) {
            $msg = sprintf('Index "%s" deleted sucessfully!', $indexName);
            $output->writeln($msg);

            return;
        }

        $output->writeln('[Error]' . $response->getErrorMessage());
    }
}
