<?php

namespace Brisum\Deploy\Console;

use Brisum\Deploy\Lib\Client\ClientFactory;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FilesFetchCommand extends Command
{
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        parent::__construct();

        $this->clientFactory = $clientFactory;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('files:fetch')

            ->addArgument('configuration', InputArgument::REQUIRED, 'The name of server configuration')

            // the short description shown while running "php bin/console list"
            ->setDescription('Fetch file list.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $input->getArgument('configuration');
        $configPath = DEPLOY_CONFIG_DIR . $configuration . '.json';

        if (!file_exists($configPath)) {
            throw new Exception("Not found configuration " . $configuration);
        }

        $config = json_decode(file_get_contents($configPath), true);
        $client = $this->clientFactory->create($config);

        if(!$client->connect()) {
            throw new Exception('Can not connect to ' . $configuration);
        }
        $client->load();

        if (!file_exists(DEPLOY_TMP_DIR_FILES) && !mkdir(DEPLOY_TMP_DIR_FILES, 0777, true)) {
            throw new Exception('Can not create directory ' . DEPLOY_TMP_DIR_FILES);
        }

        echo $client->getFilelist(DEPLOY_TMP_DIR_FILES . $configuration .'-files.txt');

        $client->clear();
        $client->close();
    }
}
