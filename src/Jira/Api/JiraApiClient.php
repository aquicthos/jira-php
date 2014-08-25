<?php

namespace Jira\Api;

use Guzzle\Common\Collection;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;

class JiraApiClient extends Client
{
    public static function factory($config = array())
    {
        // Provide a hash of default client configuration options.
        $default = array(
            'base_url' => '@base_url@'
        );

        // The following values are required when creating the client.
        $required = array(
            'authentication',
        );

        // Merge in default settings and validate the config
        $config = Collection::fromConfig($config, $default, $required);

        // Create a new client.
        $client = new self($config->get('base_url'), $config);

        // Plugin authentication.
        $auth = $config->get('authentication');
        if ($auth['method'] == 'Basic') {
            $client->getConfig()->setPath('request.options/auth', array($auth['jira_username'], $auth['password'], 'Basic|Digest'));
        }

        // Set the service description.
        $pathToServiceDescription = __DIR__ . '/../../lib/JiraApiClient.json';
        $client->setDescription(ServiceDescription::factory($pathToServiceDescription));

        return $client;
    }

}
