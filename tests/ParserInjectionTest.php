<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Contracts\DeviceDetectorFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\AbstractBotParser;
use DeviceDetector\Parser\Client\AbstractClientParser;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\Definition;

class ParserInjectionTest extends TestCase
{
    /**
     * @var Kernel
     */
    private static $kernel;

    /**
     * @before
     */
    public function setupKernel(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector_factory.public', DeviceDetectorFactoryInterface::class));

        self::$kernel = $kernel;
    }

    /**
     * @dataProvider botParsers
     */
    public function testBotParserInjectionFactory(Definition $definition, bool $shouldFind): void
    {
        self::$kernel->appendDefinition('custom_parser', $definition);

        self::$kernel->boot();

        $parser = self::$kernel->getContainer()->get('custom_parser');

        /** @var DeviceDetectorFactoryInterface $factory */
        $factory = self::$kernel->getContainer()->get('device_detector_factory.public');
        $deviceDetector = $factory->createDeviceDetector();

        if ($shouldFind) {
            static::assertContains($parser, $deviceDetector->getBotParsers());
        } else {
            static::assertNotContains($parser, $deviceDetector->getBotParsers());
        }
    }

    /**
     * @dataProvider botParsers
     */
    public function testBotParserInjection(Definition $definition, bool $shouldFind): void
    {
        self::$kernel->appendDefinition('custom_parser', $definition);

        self::$kernel->boot();

        $parser = self::$kernel->getContainer()->get('custom_parser');

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = self::$kernel->getContainer()->get('device_detector.public');

        if ($shouldFind) {
            static::assertContains($parser, $deviceDetector->getBotParsers());
        } else {
            static::assertNotContains($parser, $deviceDetector->getBotParsers());
        }
    }

    /**
     * @return iterable<string, array{Definition, bool}>
     */
    public function botParsers(): iterable
    {
        yield 'not configured' => [
            (new Definition(CustomBotParser::class))
                ->setPublic(true),
            false,
        ];

        yield 'tagged' => [
            (new Definition(CustomBotParser::class))
                ->setPublic(true)
                ->addTag('acsiomatic.device_detector.bot_parser'),
            true,
        ];

        yield 'auto configured' => [
            (new Definition(CustomBotParser::class))
                ->setPublic(true)
                ->setAutoconfigured(true),
            true,
        ];
    }

    /**
     * @dataProvider clientParsers
     */
    public function testClientParserInjectionFactory(Definition $definition, bool $shouldFind): void
    {
        self::$kernel->appendDefinition('custom_parser', $definition);

        self::$kernel->boot();

        $parser = self::$kernel->getContainer()->get('custom_parser');

        /** @var DeviceDetectorFactoryInterface $factory */
        $factory = self::$kernel->getContainer()->get('device_detector_factory.public');
        $deviceDetector = $factory->createDeviceDetector();

        if ($shouldFind) {
            static::assertContains($parser, $deviceDetector->getClientParsers());
        } else {
            static::assertNotContains($parser, $deviceDetector->getClientParsers());
        }
    }

    /**
     * @dataProvider clientParsers
     */
    public function testClientParserInjection(Definition $definition, bool $shouldFind): void
    {
        self::$kernel->appendDefinition('custom_parser', $definition);

        self::$kernel->boot();

        $parser = self::$kernel->getContainer()->get('custom_parser');

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = self::$kernel->getContainer()->get('device_detector.public');

        if ($shouldFind) {
            static::assertContains($parser, $deviceDetector->getClientParsers());
        } else {
            static::assertNotContains($parser, $deviceDetector->getClientParsers());
        }
    }

    /**
     * @return iterable<string, array{Definition, bool}>
     */
    public function clientParsers(): iterable
    {
        yield 'not configured' => [
            (new Definition(CustomClientParser::class))
                ->setPublic(true),
            false,
        ];

        yield 'auto configured' => [
            (new Definition(CustomClientParser::class))
                ->setPublic(true)
                ->setAutoconfigured(true),
            true,
        ];

        yield 'tagged' => [
            (new Definition(CustomClientParser::class))
                ->setPublic(true)
                ->addTag('acsiomatic.device_detector.client_parser'),
            true,
        ];
    }

    /**
     * @dataProvider deviceParsers
     */
    public function testDeviceParserInjectionFactory(Definition $definition, bool $shouldFind): void
    {
        self::$kernel->appendDefinition('custom_parser', $definition);

        self::$kernel->boot();

        $parser = self::$kernel->getContainer()->get('custom_parser');

        /** @var DeviceDetectorFactoryInterface $factory */
        $factory = self::$kernel->getContainer()->get('device_detector_factory.public');
        $deviceDetector = $factory->createDeviceDetector();

        if ($shouldFind) {
            static::assertContains($parser, $deviceDetector->getDeviceParsers());
        } else {
            static::assertNotContains($parser, $deviceDetector->getDeviceParsers());
        }
    }

    /**
     * @dataProvider deviceParsers
     */
    public function testDeviceParserInjection(Definition $definition, bool $shouldFind): void
    {
        self::$kernel->appendDefinition('custom_parser', $definition);

        self::$kernel->boot();

        $parser = self::$kernel->getContainer()->get('custom_parser');

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = self::$kernel->getContainer()->get('device_detector.public');

        if ($shouldFind) {
            static::assertContains($parser, $deviceDetector->getDeviceParsers());
        } else {
            static::assertNotContains($parser, $deviceDetector->getDeviceParsers());
        }
    }

    /**
     * @return iterable<string, array{Definition, bool}>
     */
    public function deviceParsers(): iterable
    {
        yield 'no configured' => [
            (new Definition(CustomDeviceParser::class))
                ->setPublic(true),
            false,
        ];

        yield 'auto configured' => [
            (new Definition(CustomDeviceParser::class))
                ->setPublic(true)
                ->setAutoconfigured(true),
            true,
        ];

        yield 'tagged' => [
            (new Definition(CustomDeviceParser::class))
                ->setPublic(true)
                ->addTag('acsiomatic.device_detector.device_parser'),
            true,
        ];
    }
}

final class CustomBotParser extends AbstractBotParser
{
    public function discardDetails(): void
    {
    }

    /**
     * @return array<mixed>
     */
    public function parse(): ?array
    {
        return null;
    }
}

final class CustomClientParser extends AbstractClientParser
{
}

final class CustomDeviceParser extends AbstractDeviceParser
{
}
