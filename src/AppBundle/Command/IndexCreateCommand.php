<?php

namespace AppBundle\Command;

use DateTime;
use Elastica\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCreateCommand extends ContainerAwareCommand
{
    public const DEFAULT_NUM_SHARDS = 1;
    public const DEFAULT_NUM_REPLICAS = 0;

    /**
     * @var string
     */
    protected $elasticType;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor
     *
     * @param string $elasticType
     * @param Client $client
     */
    public function __construct(string $elasticType, Client $client)
    {
        $this->elasticType = $elasticType;
        $this->client = $client;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $currentDate = new DateTime();

        $defaultIndex = $this->elasticType . '_' . $currentDate->format('Y_m_d');

        $this->setName('index:create')
            ->setDescription('Creates an index with a given name')
            ->addArgument(
                'index',
                InputArgument::OPTIONAL,
                'The index name',
                $defaultIndex
            )
            ->addOption(
                'number-of-shards',
                null,
                InputOption::VALUE_OPTIONAL,
                'The number of primary index shards',
                self::DEFAULT_NUM_SHARDS
            )
            ->addOption(
                'number-of-replicas',
                null,
                InputOption::VALUE_OPTIONAL,
                'The number of replicated shards',
                self::DEFAULT_NUM_REPLICAS
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

        if ($index->exists()) {
            $msg = sprintf('Index "%s" already exists!', $indexName);
            $output->writeln($msg);

            return;
        }

        $options = $this->createIndexArgs($input, $output);

        $response = $index->create($options);
        if ($response->isOk()) {
            $msg = sprintf('Index "%s" created sucessfully!', $indexName);
            $output->writeln($msg);

            return;
        }

        $output->writeln('[Error]' . $response->getErrorMessage());
    }

    /**
     * Helper function to form index create parameters
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    private function createIndexArgs(InputInterface $input, OutputInterface $output): array
    {
        return [
            'settings' => [
                'index' => [
                    'number_of_shards' => (int) $input->getOption('number-of-shards'),
                    'number_of_replicas' => (int) $input->getOption('number-of-replicas'),
                ]
            ]
        ];
    }
}
