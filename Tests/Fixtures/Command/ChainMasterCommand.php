<?php

namespace DevTym\ChainCommandBundle\Tests\Fixtures\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A test master command used to demonstrate command chaining.
 */
#[AsCommand(
    name:  'master:hello',
    description: 'foo:hello!'
)]
class ChainMasterCommand extends Command
{
    /**
     * Executes the master command logic.
     *
     * @param InputInterface $input The input instance
     * @param OutputInterface $output The output instance
     *
     * @return int Exit code (0 = success)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('MasterCommand Hello!!');
        return Command::SUCCESS;
    }
}
