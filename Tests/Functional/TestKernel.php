<?php

namespace DevTym\ChainCommandBundle\Tests\Functional;

use Exception;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DevTym\ChainCommandBundle\ChainCommandBundle;
use DevTym\ChainCommandBundle\Tests\Fixtures\Command\ChainMasterCommand;
use DevTym\ChainCommandBundle\Tests\Fixtures\Command\ChainMemberCommand;

/**
 * Custom Kernel used for functional testing of the ChainCommandBundle.
 */
class TestKernel extends Kernel
{
    /**
     * Registers bundles required for the test environment.
     *
     * @return iterable
     */
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new ChainCommandBundle(),
        ];
    }

    /**
     * Loads container configuration files specific to the test environment.
     *
     * @param LoaderInterface $loader
     * @return void
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/framework.yaml');
        $loader->load(__DIR__.'/config/chain_command.yaml');
    }

    /**
     * Builds the container by registering test-specific services.
     *
     * @param ContainerBuilder $container
     * @return void
     */
    protected function build(ContainerBuilder $container): void
    {
        $container->register(ChainMasterCommand::class)->addTag('console.command');
        $container->register(ChainMemberCommand::class)->addTag('console.command');
    }
}
