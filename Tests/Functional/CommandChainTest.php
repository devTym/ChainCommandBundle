<?php

namespace DevTym\ChainCommandBundle\Tests\Functional;

use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Functional tests for ChainCommandBundle command chaining behavior.
 */
class CommandChainTest extends KernelTestCase
{
    /**
     * Tests that executing the master command also executes its member commands.
     *
     * @throws Exception
     */
    public function testChainMasterCommandExecutes(): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new StringInput('master:hello');
        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);
        $result = $output->fetch();

        $this->assertSame(113, $exitCode);
        $this->assertStringContainsString('MasterCommand Hello!!', $result);
        $this->assertStringContainsString('MemberCommand Hello!!', $result);
    }

    /**
     * Tests that executing a chain member command directly shows an error and does not execute it.
     *
     * @throws Exception
     */
    public function testChainMemberCommandExecutes(): void
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new StringInput('member:hello');
        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);
        $result = $output->fetch();

        $this->assertSame(113, $exitCode);
        $this->assertStringContainsString('is a member of master:hello command chain and cannot be executed on its own', $result);
    }
}
