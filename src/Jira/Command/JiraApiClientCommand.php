<?php

namespace Jira\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JiraApiClientCommand extends BaseCommand
{
    protected $operation;

    public function __construct($operation)
    {
        $this->operation = $operation;
        $operationArray = $operation->toArray();
        $commandName = $operation->getName();
        parent::__construct($commandName);
        $this->setDescription($operation->getSummary());

        foreach($this->operation->getParams() as $parameter) {
            if ($parameter->getRequired()) {
                $this->addArgument($parameter->getName(), InputArgument::REQUIRED, $parameter->getDescription());
            } else {
                $optValue = ($parameter->getType() == 'boolean') ? InputOption::VALUE_NONE : InputOption::VALUE_REQUIRED;
                $this->addOption($parameter->getName(), null, $optValue, $parameter->getDescription());
            }
        }

        $helpText = "<info>{$commandName}</info>: {$operationArray['summary']}";
        $helpText .= "\n\n" . "<info>php jira.php {$commandName} options</info>";
        if (!empty($operationArray['data']['documentation'])) {
            $helpText .= "\n\nDocumentation: {$operationArray['data']['documentation']}";
        }

        $this->setHelp($helpText);
    }

    protected function configure()
    {
    }

    protected function isDefaultConsoleOption($optionName)
    {
        $isDefaultConsoleOption = false;
        switch ($optionName) {
            case 'ansi':
            case 'help':
            case 'no-ansi':
            case 'no-interaction':
            case 'quiet':
            case 'verbose':
            case 'version':
                $isDefaultConsoleOption = true;
                break;
        }
        return $isDefaultConsoleOption;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getApplication()->getApiClient();

        $method = $this->operation->getName();
        $arguments = array();
        $argumentWidth = 1;
        foreach ($this->getDefinition()->getArguments() as $arg) {
            $argName = $arg->getName();
            if ($argName == 'command') {
                continue;
            }
            if (strlen($argName) > $argumentWidth) {
                $argumentWidth = strlen($argName);
            }
            $arguments[$argName] = $input->getArgument($argName);
        }

        foreach ($this->getDefinition()->getOptions() as $option) {
            $optionName = $option->getName();
            if ($this->isDefaultConsoleOption($optionName)) {
                continue;
            }
            if ($option = $input->getOption($optionName)) {
                if (strlen($optionName) > $argumentWidth) {
                    $argumentWidth = strlen($optionName);
                }
                $arguments[$optionName] = $option;
            }
        }

        $output->writeln(sprintf("\n<info>Calling: <comment>$method</comment>%s:</info>", count($arguments) ? " with these parameters" : ''));
        foreach ($arguments as $key => $value) {
            $output->writeln(sprintf("\t<info>%-{$argumentWidth}s</info>: %s", $key, $value));
        }

        $result = call_user_func_array(
            array($client, $method),
            array($arguments)
        );
        $output->writeln($this->processResponse($result));
    }

    protected function processResponse($result)
    {
        $output = "\n<info>Response from API:</info>\n";
        $output .= print_r($result, TRUE);
        return $output;
    }
}
