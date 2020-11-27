<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests;

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler\CompilerPassFactory;
use Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel\Kernel;
use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class UserAgentSourceTest extends TestCase
{
    /**
     * @return void
     */
    public function testDeviceDetectorServiceAssumesMasterRequestUserAgent()
    {
        $kernel = new Kernel('test', true);
        $kernel->appendBundle(new FrameworkBundle());
        $kernel->appendBundle(new AcsiomaticDeviceDetectorBundle());
        $kernel->appendExtensionConfiguration('framework', ['test' => true, 'secret' => '53CR37']);
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('device_detector.public', DeviceDetector::class));
        $kernel->appendCompilerPass(CompilerPassFactory::createPublicAlias('request_stack.public', RequestStack::class));

        $kernel->boot();

        $masterRequest = new Request();
        $masterRequest->headers->set('user-agent', 'MASTER');

        $currentRequest = new Request();
        $currentRequest->headers->set('user-agent', 'CURRENT');

        /** @var RequestStack $requestStack */
        $requestStack = $kernel->getContainer()->get('request_stack.public');
        $requestStack->push($masterRequest);
        $requestStack->push($currentRequest);

        /** @var DeviceDetector $deviceDetector */
        $deviceDetector = $kernel->getContainer()->get('device_detector.public');

        static::assertSame('MASTER', $deviceDetector->getUserAgent());
    }
}
