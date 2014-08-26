<?php

namespace Jira;

use Jira\Api\JiraApiClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class Jira
{
    protected $config;
    protected $api;
    protected $operations;

    public function __construct()
    {
        $this->api = null;
        $this->loadConfig();
        $this->getApiClient();
        $this->buildOperations();
    }

    /**
     * Magic __call method to automatically service calls.
     */
    public function __call($method, $args)
    {
        if (isset($this->operations[$method])) {
            $lp = count($args) - 1;

            if (is_array($args[$lp])) {
                $call_args = array_pop($args);
            } else {
                $call_args = null;
            }

            return $this->restCall($this->operations[$method], $call_args);
        }

        throw new \BadMethodCallException;
    }

    function restCall($method, $args) {
        $client = $this->getApiClient();
        return call_user_func_array(
            array($client, $method),
            array($args)
        );
    }

    protected function loadConfig()
    {
        $configValues = array();

        $configDirectories = array(
            __DIR__ . '/../../lib',
            '/etc/jira-api',
            getenv('HOME') . '/.jira-api',
        );

        $locator = new FileLocator($configDirectories);
        $jiraApiConfig = $locator->locate('jira-api.yml', null, false);

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

    protected function buildOperations()
    {
        $operations = array();
        $client = $this->getApiClient();
        $apiDescription = $client->getDescription();

        /**
         * @var $operation \Guzzle\Service\Description\Operation
         */
        foreach ($apiDescription->getOperations() as $operation) {
            $commandName = $operation->getName();
            $operations[$commandName] = $commandName;
        }

        $this->operations = $operations;
    }

}