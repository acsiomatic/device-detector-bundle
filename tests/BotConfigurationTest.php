<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

final class BotConfigurationTest extends TestCase
{
    /**
     * @var string
     */
    private const BOT_USER_AGENT = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

    public function testBotDetectionMustNotBeSkippedByDefault(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        static::assertTrue($deviceDetector->isBot());
    }

    public function testSkippingBotDetection(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['bot' => ['skip_detection' => true]]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        static::assertFalse($deviceDetector->isBot());
    }

    public function testDoNotSkipBotDetection(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['bot' => ['skip_detection' => false]]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        static::assertTrue($deviceDetector->isBot());
    }

    public function testBotInformationMustNotBeDiscardedByDefault(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        /** @var array<mixed> $botInfo */
        $botInfo = $deviceDetector->getBot();

        static::assertArrayHasKey('name', $botInfo);
    }

    public function testDiscardingBotInformation(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['bot' => ['discard_information' => true]]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        static::assertTrue($deviceDetector->isBot());

        $botInfo = $deviceDetector->getBot();

        static::assertIsArray($botInfo);
        static::assertArrayNotHasKey('name', $botInfo);
    }

    public function testDoNotDiscardBotInformation(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['bot' => ['discard_information' => false]]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        static::assertNotEmpty($deviceDetector->getBot());
    }
}
