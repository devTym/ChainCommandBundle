<?php

namespace DevTym\ChainCommandBundle\Tests\Fixtures\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A test member command used to demonstrate being part of a command chain.
 */
#[AsCommand(
    name:  'member:hello',
    description: 'bar:hi!'
)]
class ChainMemberCommand extends Command
{
    /**
     * Executes the member command logic.
     *
     * @param InputInterface $input The input instance
     * @param OutputInterface $output The output instance
     *
     * @return int Exit code (0 = success)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('MemberCommand Hello!!');
        return Command::SUCCESS;
    }
}
