<?php

namespace Acsiomatic\DeviceDetectorBundle\Twig;

use DeviceDetector\DeviceDetector;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * @internal
 * @readonly
 */
final class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly string $name,
        private readonly DeviceDetector $detector,
    ) {}

    /**
     * @return array<string, DeviceDetector>
     */
    public function getGlobals(): array
    {
        return [
            $this->name => $this->detector,
        ];
    }
}
