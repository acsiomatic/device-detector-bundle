<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

final class VersionTruncationTest extends TestCase
{
    /**
     * @var string
     */
    private const USER_AGENT = 'Mozilla/5.0 (Linux; Android 4.2.2; ARCHOS 101 PLATINUM Build/JDQ39) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Safari/537.36';

    public function testDefaultsTargetsToMinor(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();

        $kernel->appendCompilerPass(
            CompilerPassFactory::createPublicAlias(
                'device_detector_factory.public',
                DeviceDetectorFactoryInterface::class
            )
        );

        $kernel->boot();

        /** @var DeviceDetectorFactoryInterface $deviceDetectorFactory */
        $deviceDetectorFactory = $kernel->getContainer()->get('device_detector_factory.public');
        $deviceDetector = $deviceDetectorFactory->createDeviceDetector();

        $deviceDetector->setUserAgent(self::USER_AGENT);
        $deviceDetector->parse();

        static::assertSame('4.2', $deviceDetector->getOs('version'));
        static::assertSame('34.0', $deviceDetector->getClient('version'));
    }

    #[DataProvider('getVersionTruncationFixtures')]
    public function testSetVersionTruncation(
        string $userAgent,
        string $truncation,
        string $expectedOsVersion,
        string $expectedClientVersion
    ): void {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['version_truncation' => $truncation]);

        $kernel->appendCompilerPass(
            CompilerPassFactory::createPublicAlias(
                'device_detector_factory.public',
                DeviceDetectorFactoryInterface::class
            )
        );

        $kernel->boot();

        /** @var DeviceDetectorFactoryInterface $deviceDetectorFactory */
        $deviceDetectorFactory = $kernel->getContainer()->get('device_detector_factory.public');
        $deviceDetector = $deviceDetectorFactory->createDeviceDetector();

        $deviceDetector->setUserAgent($userAgent);
        $deviceDetector->parse();

        static::assertSame($expectedOsVersion, $deviceDetector->getOs('version'));
        static::assertSame($expectedClientVersion, $deviceDetector->getClient('version'));
    }

    /**
     * @return iterable<array{user_agent: string, truncation: string, os_version: string, client_version: string}>
     */
    public static function getVersionTruncationFixtures(): iterable
    {
        yield 'none' => [
            'user_agent' => self::USER_AGENT,
            'truncation' => 'none',
            'os_version' => '4.2.2',
            'client_version' => '34.0.1847.114',
        ];

        yield 'build' => [
            'user_agent' => self::USER_AGENT,
            'truncation' => 'build',
            'os_version' => '4.2.2',
            'client_version' => '34.0.1847.114',
        ];

        yield 'patch' => [
            'user_agent' => self::USER_AGENT,
            'truncation' => 'patch',
            'os_version' => '4.2.2',
            'client_version' => '34.0.1847',
        ];

        yield 'minor' => [
            'user_agent' => self::USER_AGENT,
            'truncation' => 'minor',
            'os_version' => '4.2',
            'client_version' => '34.0',
        ];

        yield 'major' => [
            'user_agent' => self::USER_AGENT,
            'truncation' => 'major',
            'os_version' => '4',
            'client_version' => '34',
        ];
    }
}
