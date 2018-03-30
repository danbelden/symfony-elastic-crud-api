<?php

namespace AppBundle\Command;

use Elastica\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AliasDeleteCommand extends ContainerAwareCommand
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
        $this->setName('index:alias:delete')
            ->setDescription('Deletes an alias connected to a given index')
            ->addArgument(
                'index',
                InputArgument::REQUIRED,
                'The index name'
            )
            ->addArgument(
                'alias',
                InputArgument::REQUIRED,
                'The alias to add too it'
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

        $aliasName = $input->getArgument('alias');

        $response = $index->removeAlias($aliasName);
        if ($response->isOk()) {
            $msg = sprintf('Alias "%s" was removed from index "%s" sucessfully!', $aliasName, $indexName);
            $output->writeln($msg);

            return;
        }

        $output->writeln('[Error]' . $response->getErrorMessage());
    }
}
