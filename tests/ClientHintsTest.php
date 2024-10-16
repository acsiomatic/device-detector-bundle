<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Contracts\ClientHintsFactoryInterface;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ClientHintsTest extends TestCase
{
    public function testDeviceDetectorReceivesClientHints(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('request_stack.public', RequestStack::class));

        $kernel->boot();

        $request = new Request();
        $request->headers->set('Sec-CH-UA-Platform', 'Windows');
        $request->headers->set('Sec-CH-UA-Platform-Version', '10.0.0');

        /** @var RequestStack $requestStack */
        $requestStack = $kernel->getContainer()->get('request_stack.public');
        $requestStack->push($request);

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');
        $clientHints = $deviceDetector->getClientHints();

        static::assertInstanceOf(ClientHints::class, $clientHints);
        static::assertSame('Windows', $clientHints->getOperatingSystem());
        static::assertSame('10.0.0', $clientHints->getOperatingSystemVersion());
    }

    public function testFactoryCreatesClientHintsFromRequest(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendDefaultFrameworkExtensionConfiguration();
        $kernel->appendCompilerPass(
            CompilerPassFactory::createPublicAlias(
                'client_hints_factory.public',
                ClientHintsFactoryInterface::class,
            ),
        );

        $kernel->boot();

        /** @var ClientHintsFactoryInterface $clientHintsFactory */
        $clientHintsFactory = $kernel->getContainer()->get('client_hints_factory.public');

        $request = new Request();
        $request->headers->set('Sec-CH-UA-Platform', 'Windows');
        $request->headers->set('Sec-CH-UA-Platform-Version', '10.0.0');

        $clientHints = $clientHintsFactory->createClientHintsFromRequest($request);

        static::assertSame('Windows', $clientHints->getOperatingSystem());
        static::assertSame('10.0.0', $clientHints->getOperatingSystemVersion());
    }
}
