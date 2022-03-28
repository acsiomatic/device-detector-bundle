<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

final class AutoParseTest extends TestCase
{
    /**
     * @var Kernel
     */
    private static $kernelDefault;

    /**
     * @var Kernel
     */
    private static $kernelDisabled;

    /**
     * @beforeClass
     */
    public static function setUpKernel(): void
    {
        $kernelDefault = new Kernel('test', true);

        $kernelDefault->appendBundle(new FrameworkBundle());
        $kernelDefault->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernelDefault->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernelDefault->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernelDefault->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector_factory.public', DeviceDetectorFactoryInterface::class));

        $kernelDefault->boot();

        self::$kernelDefault = $kernelDefault;

        $kernelDisabled = new Kernel('test', true);

        $kernelDisabled->appendBundle(new FrameworkBundle());
        $kernelDisabled->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernelDisabled->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernelDisabled->appendExtensionConfiguration('acsiomatic_device_detector', ['auto_parse' => false]);
        $kernelDisabled->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernelDisabled->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector_factory.public', DeviceDetectorFactoryInterface::class));

        $kernelDisabled->boot();

        self::$kernelDisabled = $kernelDisabled;
    }

    public function testParserIsNotCalledByDefault(): void
    {
        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = self::$kernelDefault->getContainer()->get('device_detector.public');

        static::assertFalse($deviceDetector->isParsed());
    }

    public function testParserIsNotCalledByDefaultInInstanceFromFactory(): void
    {
        /** @var DeviceDetectorFactoryInterface $factory */
        $factory = self::$kernelDefault->getContainer()->get('device_detector_factory.public');
        $deviceDetector = $factory->createDeviceDetector();

        static::assertFalse($deviceDetector->isParsed());
    }

    /**
     * @dataProvider nonTriggersMethods
     *
     * @param mixed ...$arguments
     */
    public function testParserIsNotTriggeredFor(string $method, ...$arguments): void
    {
        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = self::$kernelDefault->getContainer()->get('device_detector.public');
        $deviceDetector->setUserAgent('Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0');

        static::assertFalse($deviceDetector->isParsed());

        try {
            $deviceDetector->$method(...$arguments);
        } catch (\BadMethodCallException $badMethodCallException) {
            static::markTestSkipped(sprintf('Method "%s" not available in this version', $method));
        }

        static::assertFalse($deviceDetector->isParsed());
    }

    /**
     * @return iterable<string[]>
     */
    public function nonTriggersMethods(): iterable
    {
        yield 'discardBotInformation()' => ['discardBotInformation'];
        yield 'getUserAgent()' => ['getUserAgent'];
        yield 'setUserAgent()' => ['setUserAgent', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0'];
        yield 'skipBotDetection()' => ['skipBotDetection'];
    }

    /**
     * @dataProvider triggersMethods
     * @dataProvider triggersTypeMagicMethods
     * @dataProvider triggersClientMagicTypeMethods
     */
    public function testParserIsTriggeredIfAutoParserIsEnabled(string $method): void
    {
        /** @var DeviceDetectorFactoryInterface $factory */
        $factory = self::$kernelDefault->getContainer()->get('device_detector_factory.public');

        $deviceDetector = $factory->createDeviceDetector();
        $deviceDetector->setUserAgent('Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0');

        static::assertFalse($deviceDetector->isParsed());

        try {
            $deviceDetector->$method();
        } catch (\BadMethodCallException $badMethodCallException) {
            static::markTestSkipped(sprintf('Method "%s" not available in this version', $method));
        }

        static::assertTrue($deviceDetector->isParsed());
    }

    /**
     * @dataProvider triggersMethods
     * @dataProvider triggersTypeMagicMethods
     * @dataProvider triggersClientMagicTypeMethods
     *
     * @param mixed ...$arguments
     */
    public function testParserIsNotCalledIfAutoParseIsDisabled(string $method, ...$arguments): void
    {
        /** @var DeviceDetectorFactoryInterface $factory */
        $factory = self::$kernelDisabled->getContainer()->get('device_detector_factory.public');
        $deviceDetector = $factory->createDeviceDetector();

        static::assertFalse($deviceDetector->isParsed());

        try {
            $deviceDetector->$method(...$arguments);
        } catch (\BadMethodCallException $badMethodCallException) {
            static::markTestSkipped(sprintf('Method "%s" not available in this version', $method));
        }

        static::assertFalse($deviceDetector->isParsed());
    }

    /**
     * @return iterable<string[]>
     */
    public function triggersMethods(): iterable
    {
        yield 'getBot()' => ['getBot'];
        yield 'getBrand()' => ['getBrand'];
        yield 'getBrandName()' => ['getBrandName'];
        yield 'getClient()' => ['getClient'];
        yield 'getDevice()' => ['getDevice'];
        yield 'getDeviceName()' => ['getDeviceName'];
        yield 'getModel()' => ['getModel'];
        yield 'getOs()' => ['getOs'];
        yield 'isBot()' => ['isBot'];
        yield 'isDesktop()' => ['isDesktop'];
        yield 'isMobile()' => ['isMobile'];
    }

    /**
     * @return iterable<string[]>
     */
    public function triggersTypeMagicMethods(): iterable
    {
        yield 'isCamera()' => ['isCamera'];
        yield 'isCarBrowser()' => ['isCarBrowser'];
        yield 'isConsole()' => ['isConsole'];
        yield 'isFeaturePhone()' => ['isFeaturePhone'];
        yield 'isPeripheral()' => ['isPeripheral'];
        yield 'isPhablet()' => ['isPhablet'];
        yield 'isPortableMediaPlayer()' => ['isPortableMediaPlayer'];
        yield 'isSmartDisplay()' => ['isSmartDisplay'];
        yield 'isSmartSpeaker()' => ['isSmartSpeaker'];
        yield 'isSmartphone()' => ['isSmartphone'];
        yield 'isTV()' => ['isTV'];
        yield 'isTablet()' => ['isTablet'];
        yield 'isWearable()' => ['isWearable'];
    }

    /**
     * @return iterable<string[]>
     */
    public function triggersClientMagicTypeMethods(): iterable
    {
        yield 'isBrowser()' => ['isBrowser'];
        yield 'isFeedReader()' => ['isFeedReader'];
        yield 'isLibrary()' => ['isLibrary'];
        yield 'isMediaPlayer()' => ['isMediaPlayer'];
        yield 'isMobileApp()' => ['isMobileApp'];
        yield 'isPIM()' => ['isPIM'];
    }
}
