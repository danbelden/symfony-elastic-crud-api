<?php

namespace AppBundle\Command;

use Elastica\Client;
use Elastica\Request;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class AliasMoveCommand extends ContainerAwareCommand
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
        $this->setName('index:alias:move')
            ->setDescription('Moves an alias from one index to another atomically')
            ->addArgument(
                'source-index',
                InputArgument::REQUIRED,
                'The index to move the alias from'
            )
            ->addArgument(
                'target-index',
                InputArgument::REQUIRED,
                'The index to move the alias too'
            )
            ->addArgument(
                'alias',
                InputArgument::REQUIRED,
                'The alias to move'
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
        $sourceIndexName = $input->getArgument('source-index');
        $sourceIndex = $this->client->getIndex($sourceIndexName);
        if ($sourceIndex->exists() === false) {
            $msg = sprintf('[Error] Source index "%s" does not exist!', $sourceIndexName);
            $output->writeln($msg);

            return;
        }

        $aliasName = $input->getArgument('alias');

        $sourceAliases = $sourceIndex->getAliases();
        if (!in_array($aliasName, $sourceAliases, true)) {
            $msg = sprintf(
                '[Error] Alias "%s" is not connected to source index "%s"!',
                $aliasName,
                $sourceIndexName
            );
            $output->writeln($msg);

            return;
        }

        $targetIndexName = $input->getArgument('target-index');
        $targetIndex = $this->client->getIndex($targetIndexName);
        if ($targetIndex->exists() === false) {
            $msg = sprintf('[Error] Target index "%s" does not exist!', $targetIndexName);
            $output->writeln($msg);

            return;
        }

        $targetAliases = $targetIndex->getAliases();

        $aliasConnectedToTarget = in_array($aliasName, $targetAliases, true);
        if ($aliasConnectedToTarget) {
            $msg = sprintf(
                'Alias "%s" is already connected to target index "%s"!',
                $aliasName,
                $targetIndexName
            );
            $output->writeln($msg);

            // https://symfony.com/doc/3.4/components/console/helpers/questionhelper.html
            $questionHelper = $this->getHelper('question');
            assert($questionHelper instanceof \Symfony\Component\Console\Helper\QuestionHelper);
            $question = new ConfirmationQuestion('Continue with this action? [y/n]', false);

            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('Cancelled.');

                return;
            }
        }

        if ($aliasConnectedToTarget) {
            $response = $sourceIndex->removeAlias($aliasName);
            if ($response->isOk()) {
                $msg = sprintf(
                    'Alias "%s" was removed from source index "%s"!',
                    $aliasName,
                    $sourceIndexName
                );
                $output->writeln($msg);

                return;
            }

            $output->writeln($response->getErrorMessage());

            return;
        }

        $query = [
            'actions' => [
                [
                    'remove' => [
                        'index' => $sourceIndexName,
                        'alias' => $aliasName
                    ],
                ],
                [
                    'add' => [
                        'index' => $targetIndexName,
                        'alias' => $aliasName
                    ]
                ]
            ]
        ];
        $jsonQuery = json_encode($query);

        // https://www.elastic.co/guide/en/elasticsearch/guide/current/index-aliases.html
        $response = $this->client->request('_aliases', Request::POST, $jsonQuery);
        if ($response->isOk() === false) {
            $output->writeln($response->getErrorMessage());

            return;
        }

        $msg = sprintf(
            'Alias "%s" was moved from source index "%s" to target index "%s"!',
            $aliasName,
            $sourceIndexName,
            $targetIndexName
        );
        $output->writeln($msg);
    }
}
