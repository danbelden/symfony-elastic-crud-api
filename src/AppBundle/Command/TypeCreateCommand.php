<?php

namespace AppBundle\Command;

use DateTime;
use Elastica\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TypeCreateCommand extends ContainerAwareCommand
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
        $defaultPath = realpath(__DIR__ . '/../Resources/elastic/default-type-mapping.json');

        $this->setName('index:type:create')
            ->setDescription('Creates an index type with a given name')
            ->addArgument(
                'index',
                InputArgument::OPTIONAL,
                'The index name',
                $defaultIndex
            )
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'The index type to create',
                $defaultType
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'The path to the type mapping.json',
                $defaultPath
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
        if (empty($typeName)) {
            $typeName = $this->getContainer()->getParameter('elastic_type');
        }

        $type = $index->getType($typeName);
        if ($type->exists() !== false) {
            $msg = sprintf('[Error] Type "%s" allready exists!', $typeName);
            $output->writeln($msg);

            return;
        }

        $path = $input->getArgument('path');
        if (file_exists($path) === false) {
            $msg = sprintf('[Error] Mapping file path "%s" does not exist!', $path);
            $output->writeln($msg);

            return;
        }

        $mappingString = file_get_contents($path);
        $mappingArray = json_decode($mappingString, true);
        if ($mappingArray === false) {
            $msg = sprintf('[Error] Mapping file path "%s" could not be parsed!', $path);
            $output->writeln($msg);

            return;
        }

        $response = $type->setMapping($mappingArray);
        if ($response->isOk()) {
            $msg = sprintf('Type "%s/%s" created sucessfully!', $indexName, $typeName);
            $output->writeln($msg);

            return;
        }

        $output->writeln('[Error]' . $response->getErrorMessage());
    }
}
