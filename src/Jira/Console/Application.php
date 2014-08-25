<?php

namespace Jira\Console;

use Jira\Api\JiraApiClient;
use Jira\Command\JiraApiClientCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication
{
    protected $config;
    protected $api;

    public function __construct()
    {
        $this->api = null;
        $this->loadConfig();
        $this->getApiClient();
    }

    protected function loadConfig()
    {
        $configValues = array();

        $configDirectories = array(
            __DIR__ . '/../../../lib',
            '/etc/jira-api',
            getenv('HOME') . '/.jira-api',
        );

        $locator = new FileLocator($configDirectories);

        try {
            $jiraApiConfig = $locator->locate('jira-api.yml', null, false);
        } catch (\InvalidArgumentException $exception) {
            $output = new ConsoleOutput();
            $output->writeln("\t<error>                                                        </error>");
            $output->writeln("\t<error>  Couldn't find jira-api.yml. Please create it.         </error>");
            $output->writeln("\t<error>                                                        </error>");
            $output->writeln(" ");
            exit();
        }

        foreach ($jiraApiConfig as $config) {
            $configValues = array_merge($configValues, Yaml::parse($config));
        }

        $this->config = $configValues;
    }

    public function getApiClient()
    {
        $api = $this->api;
        if ($api == null) {
            if (!isset($this->config['authentication'])) {
                throw new \RuntimeException("No jira-api credentials found");
            }
            $api = JiraApiClient::factory($this->config);
            $this->api = $api;
        }
        return $api;
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $client = $this->getApiClient();
        $apiDescription = $client->getDescription();
        foreach ($apiDescription->getOperations() as $operation) {
            $commands[] = new JiraApiClientCommand($operation);
        }

        return $commands;
    }

}
