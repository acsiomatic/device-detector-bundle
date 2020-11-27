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
     * @private
     */
    const BOT_USER_AGENT = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

    /**
     * @return void
     */
    public function testBotDetectionMustNotBeSkippedByDefault()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        static::assertTrue($deviceDetector->isBot());
    }

    /**
     * @return void
     */
    public function testSkippingBotDetection()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['bot' => ['skip_detection' => true]]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        static::assertFalse($deviceDetector->isBot());
    }

    /**
     * @return void
     */
    public function testDoNotSkipBotDetection()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['bot' => ['skip_detection' => false]]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        static::assertTrue($deviceDetector->isBot());
    }

    /**
     * @return void
     */
    public function testBotInformationMustNotBeDiscardedByDefault()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
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

    /**
     * @return void
     */
    public function testDiscardingBotInformation()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['bot' => ['discard_information' => true]]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent(self::BOT_USER_AGENT);
        $deviceDetector->parse();

        /** @var array<mixed> $botInfo */
        $botInfo = $deviceDetector->getBot();

        // isset() call is a BC layer for DeviceDetector 3.x
        static::assertFalse(isset($botInfo['name']));
    }

    /**
     * @return void
     */
    public function testDoNotDiscardBotInformation()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
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
