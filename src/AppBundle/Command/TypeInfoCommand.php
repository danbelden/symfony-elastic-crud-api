<?php

namespace AppBundle\Command;

use DateTime;
use Elastica\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TypeInfoCommand extends ContainerAwareCommand
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

        $defaultType = 'models';
        $defaultIndex = $defaultType . '_' . $currentDate->format('Y_m_d');

        $this->setName('index:type:info')
            ->setDescription('List index types for a given index name')
            ->addArgument(
                'index',
                InputArgument::OPTIONAL,
                'The index name',
                $defaultIndex
            )
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'The type name',
                $defaultType
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

        $typeName = $input->getArgument('type');
        $type = $index->getType($typeName);

        if ($type->exists() === false) {
            $msg = sprintf(
                '[Error] Type `%s` does not exist in index `%s`',
                $typeName,
                $indexName
            );
            $output->writeln($msg);

            return;
        }

        $indexWithType = $indexName . '/' . $typeName;
        $seperator = str_repeat('=', strlen($indexWithType));

        $output->writeln($indexWithType);
        $output->writeln($seperator);

        $mapping = $type->getMapping();
        $mappingJson = json_encode($mapping);

        $output->writeln($mappingJson);
    }
}
