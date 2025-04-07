<?php

namespace DevTym\ChainCommandBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

/**
 * Service responsible for managing and executing chained Symfony console commands.
 */
class ChainCommandService
{
    /**
     * @param array<string, array> $chains  The configuration of master commands and their member chains
     * @param LoggerInterface $logger       PSR-3 logger used for outputting execution logs
     * @param bool $logging                 Whether logging is enabled (default true)
     */
    public function __construct(
        private readonly array $chains,
        private readonly LoggerInterface $logger,
        private readonly bool $logging = true
    ) {}

    /**
     * Symfony console application instance used to find and execute commands.
     */
    private Application $application;

    /**
     * Sets the current application instance.
     *
     * @param Application $application The Symfony console application
     */
    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    /**
     * Checks if a command is configured as a master command.
     *
     * @param string $commandName   The name of the command to check
     * @return bool                 True if the command is a master command
     */
    public function isMasterCommand(string $commandName): bool
    {
        return isset($this->chains[$commandName]);
    }

    /**
     * Finds the master command that a given member command belongs to.
     *
     * @param string $member    The member command name
     * @return string|null      The master command name or null if not found
     */
    public function getMasterForMember(string $member): ?string
    {
        foreach ($this->chains as $master => $data) {
            if (empty($data['members'])) {
                continue;
            }

            foreach ($data['members'] as $m) {
                if (($m['command'] ?? null) === $member) {
                    return $master;
                }
            }
        }

        return null;
    }


    /**
     * Executes the master command and all its chained member commands.
     *
     * @param Command $masterCommand The master command to execute
     * @param InputInterface $input The original input interface
     * @param OutputInterface $output The output interface to write responses to
     * @throws ExceptionInterface
     */
    public function executeChain(Command $masterCommand, InputInterface $input, OutputInterface $output): void
    {
        $masterName = $masterCommand->getName();

        if (!isset($this->chains[$masterName])) {
            return;
        }

        $masterConfig = $this->chains[$masterName];
        $members = $masterConfig['members'] ?? [];

        $this->logMessage(sprintf('%s is a master command of a command chain that has registered member commands', $masterName));

        foreach ($members as $member) {
            $memberName = $member['command'];
            $this->logMessage(sprintf('%s registered as a member of %s command chain', $memberName, $masterName));
        }

        $this->logMessage(sprintf('Executing %s command itself first:', $masterName));
        $configArgs = $this->chains[$masterName]['options'] ?? [];
        $args = $this->commandOptionsResolver($configArgs, $input);
        $input = new ArrayInput($args);
        $this->executeCommand($masterCommand, $input, $output);

        $this->logMessage(sprintf('Executing %s chain members:', $masterName));
        foreach ($members as $member) {
            $memberName = $member['command'];
            $args = $member['options'] ?? [];

            $memberCommand = $this->application->find($memberName);
            $this->executeCommand($memberCommand, new ArrayInput($args), $output);
        }

        $this->logMessage(sprintf('Execution of %s chain completed.', $masterName));
    }

    /**
     * Executes a single console command with the given input and logs its output.
     *
     * @param Command $command The command to run
     * @param InputInterface $input The input to pass
     * @param OutputInterface $output The output to write to
     * @throws ExceptionInterface
     */
    protected function executeCommand(Command $command, InputInterface $input, OutputInterface $output): void
    {
        $buffer = new BufferedOutput();
        $command->run($input, $buffer);
        $memberOutput = trim($buffer->fetch());
        $output->writeln($memberOutput);
        $this->logMessage($memberOutput);
    }

    /**
     * Resolves missing options from configuration and merges them into the input.
     *
     * @param array $configOptions Options from configuration
     * @param InputInterface $input Input from the user
     * @return array Merged options to pass into ArrayInput
     */
    protected function commandOptionsResolver(array $configOptions, InputInterface $input): array
    {
        $resolved = [];

        foreach ($configOptions as $name => $value) {
            if (!$input->hasParameterOption("--$name") && $input->getOption($name) === null) {
                $resolved["--$name"] = $value;
            }
        }

        return $resolved;
    }

    /**
     * Logs a message using the logger if logging is enabled.
     *
     * @param string $message   The message to log
     */
    public function logMessage(string $message): void
    {
        if ($this->logging) {
            $this->logger->info($message);
        }
    }
}
