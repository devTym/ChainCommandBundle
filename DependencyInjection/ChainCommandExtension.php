<?php

namespace DevTym\ChainCommandBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Loads and merges configuration for the ChainCommandBundle.
 */
class ChainCommandExtension extends Extension
{
    /**
     * Loads the bundle configuration and merges all chain_command sections.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $mergedConfig = $this->processConfiguration($configuration, $configs);

        $chains = [];
        foreach ($configs as $cfg) {
            foreach (($cfg['chains'] ?? []) as $master => $data) {
                if (!isset($chains[$master])) {
                    $chains[$master] = $data;
                } else {
                    // Get last master options
                    $chains[$master]['options'] = $data['options'] ?? $chains[$master]['options'] ?? [];
                    // Merge members
                    $chains[$master]['members'] = array_merge(
                        $chains[$master]['members'] ?? [],
                        $data['members'] ?? []
                    );
                }
            }
        }

        $container->setParameter('chain_command.chains', $chains);
        $container->setParameter('chain_command.logging', $mergedConfig['options']['logging'] ?? true);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }
}
