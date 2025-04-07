<?php

namespace DevTym\ChainCommandBundle\EventListener;

use DevTym\ChainCommandBundle\Service\ChainCommandService;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber that handles chained command execution for the Symfony console.
 *
 * This class listens for console command events and, if a command is part of a configured chain,
 * either executes the full chain (for master commands) or prevents independent execution (for members).
 */
class ChainCommandEventSubscriber implements EventSubscriberInterface
{
    /**
     * Whether the current execution is already part of a chain to avoid recursion.
     */
    private bool $isChainExecution = false;

    /**
     * @param ChainCommandService $service  The service that manages command chains
     */
    public function __construct(
        private readonly ChainCommandService $service
    ) {}

    /**
     * Returns the events this subscriber wants to listen to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => 'onConsoleCommand',
        ];
    }

    /**
     * Handles the execution of master and member commands based on chain configuration.
     *
     * - If a command is a master, disables its execution and manually runs the chain.
     * - If a command is a member, blocks its execution and displays an error.
     *
     * @param ConsoleCommandEvent $event The console command event
     * @throws ExceptionInterface
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        if ($this->isChainExecution) {
            return;
        }

        $command = $event->getCommand();
        if (!$command) return;

        $commandName = $command->getName();
        $master = $this->service->getMasterForMember($commandName);

        if ($this->service->isMasterCommand($commandName)) { //
            $event->disableCommand();

            $app = $command?->getApplication();
            if (!$app) {
                return;
            }

            $this->isChainExecution = true;
            $this->service->setApplication($app);
            $this->service->executeChain($command, $event->getInput(), $event->getOutput());
            $this->isChainExecution = false;
        } elseif ($master) {
            $event->getOutput()->writeln(sprintf(
                '<error>Error:</error> <comment>%s</comment> is a member of <comment>%s</comment> command chain and cannot be executed on its own.',
                $commandName,
                $master
            ));
            $event->disableCommand();
        }
    }
}
