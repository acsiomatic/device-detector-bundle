<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Twig\Environment;

final class TwigTest extends TestCase
{
    public function testDeviceDetectorMustBeAvailableByDefault(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendBundle(new TwigBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('twig', ['paths' => [__DIR__ . '/templates']]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('twig.public', 'twig'));

        $kernel->boot();

        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('twig.public');

        static::assertSame('yes', trim($twig->render('device_is_defined.txt.twig')));
    }

    public function testDeviceDetectorMustNotBeAutomaticallyParsed(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendBundle(new TwigBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('twig', ['paths' => [__DIR__ . '/templates']]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('twig.public', 'twig'));

        $kernel->boot();

        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('twig.public');

        static::assertSame('no', trim($twig->render('device_is_parsed.txt.twig')));
    }

    public function testDisablingDeviceDetectorVariableAssignment(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendBundle(new TwigBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['twig' => ['variable_name' => null]]);
        $kernel->appendExtensionConfiguration('twig', ['paths' => [__DIR__ . '/templates']]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('twig.public', 'twig'));

        $kernel->boot();

        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('twig.public');

        static::assertSame('no', trim($twig->render('device_is_defined.txt.twig')));
    }

    public function testCustomDeviceDetectorVariableName(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendBundle(new TwigBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('acsiomatic_device_detector', ['twig' => ['variable_name' => 'custom']]);
        $kernel->appendExtensionConfiguration('twig', ['paths' => [__DIR__ . '/templates']]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('twig.public', 'twig'));

        $kernel->boot();

        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('twig.public');

        static::assertSame('no', trim($twig->render('device_is_defined.txt.twig')));
        static::assertSame('yes', trim($twig->render('custom_is_defined.txt.twig')));
    }

    public function testDeviceDetectorIsTheSameAsThisFromContainer(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendBundle(new TwigBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendExtensionConfiguration('twig', ['paths' => [__DIR__ . '/templates']]);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('twig.public', 'twig'));

        $kernel->boot();

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');

        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('twig.public');

        static::assertSame('yes', trim($twig->render('device_is_same_as_given_device.txt.twig', [
            'given_device' => $deviceDetector,
        ])));
    }
}
