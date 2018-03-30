<?php

namespace AppBundle\Command;

use DateTime;
use Elastica\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TypeListCommand extends ContainerAwareCommand
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
        $currentDate = new DateTime();

        $defaultIndex = 'models_' . $currentDate->format('Y_m_d');

        $this->setName('index:type:list')
            ->setDescription('List index types for a given index name')
            ->addArgument(
                'index',
                InputArgument::OPTIONAL,
                'The index name',
                $defaultIndex
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

        $mappings = $index->getMapping();

        $typeList = array_keys($mappings);
        if (empty($typeList)) {
            $msg = sprintf('There are no types in the index `%s`!', $indexName);
            $output->writeln($msg);

            return;
        }

        $output->writeln($indexName);
        foreach ($typeList as $type) {
            $output->writeln('- ' . $type);
        }
    }
}
