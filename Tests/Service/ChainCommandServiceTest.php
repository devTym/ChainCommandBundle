<?php

namespace DevTym\ChainCommandBundle\Tests\Service;

use DevTym\ChainCommandBundle\Service\ChainCommandService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for ChainCommandService.
 */
class ChainCommandServiceTest extends TestCase
{
    /**
     * @dataProvider getChainsDataProvider
     *
     * @param array $chains
     * @param string $command
     * @param bool $expectedIsMaster
     * @param string|null $expectedMasterOfMember
     * @throws Exception
     */
    public function testExecuteChainRunsMasterAndMemberCommands($chains, $command, $expectedIsMaster, $expectedMasterOfMember) {
        $logger = $this->createMock(LoggerInterface::class);
        $service = new ChainCommandService($chains, $logger);

        $this->assertSame($expectedIsMaster, $service->isMasterCommand($command));
        $this->assertSame($expectedMasterOfMember, $service->getMasterForMember($command));
    }

    public static function getChainsDataProvider(): array
    {
        return [
            'Chain Master' => [
                [
                    'foo:hello' => [
                        'members' => [
                            ['command' => 'bar:hi']
                        ]
                    ]
                ],
                'foo:hello',
                true,
                null,
            ],
            'Chain Member' => [
                [
                    'foo:hello' => [
                        'members' => [
                            ['command' => 'bar:hi']
                        ]
                    ]
                ],
                'bar:hi',
                false,
                'foo:hello',
            ],
            'Member not in chain' => [
                [
                    'foo:hello' => [
                        'members' => [
                            ['command' => 'bar:hi']
                        ]
                    ]
                ],
                'new:hi',
                false,
                null,
            ],
            'Empty chain' => [
                [],
                'foo:hello',
                false,
                null
            ]
        ];
    }

    /**
     * @throws Exception
     */
    public function testLogMessageWithLoggingEnabled(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with('Test log');

        $service = new ChainCommandService([], $logger, true);
        $service->logMessage('Test log');
    }

    /**
     * @throws Exception
     */
    public function testLogMessageWithLoggingDisabled(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('info');

        $service = new ChainCommandService([], $logger, false);
        $service->logMessage('Should not log');
    }
}
