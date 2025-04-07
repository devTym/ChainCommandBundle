<?php

namespace DevTym\ChainCommandBundle\Tests\DependencyInjection;

use DevTym\ChainCommandBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @covers \DevTym\ChainCommandBundle\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider provideValidConfigurations
     */
    public function testValidConfiguration(array $configs, array $expected): void
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $result = $processor->processConfiguration($configuration, [$configs]);

        self::assertSame($expected, $result);
    }

    public static function provideValidConfigurations(): array
    {
        $testConfig = [
            'options' => [
                'logging' => false,
            ],
            'chains' => [
                'foo:hello' => [
                    'options' => [],
                    'members' => [
                        [
                            'command' => 'bar:hi',
                            'options' => [],
                        ],
                    ],
                ],
            ],
        ];

        return [
            'empty configuration' => [
                [],
                [
                    'options' => [
                        'logging' => true,
                    ],
                    'chains' => [],
                ],
            ],
            'single chain with members and options' => [
                $testConfig,
                $testConfig
            ],
        ];
    }
}
