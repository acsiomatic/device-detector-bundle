<?php

namespace Acsiomatic\DeviceDetectorBundle\Twig;

use DeviceDetector\DeviceDetector;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * @internal
 */
final class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var DeviceDetector
     */
    private $detector;

    public function __construct(string $name, DeviceDetector $detector)
    {
        $this->name = $name;
        $this->detector = $detector;
    }

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
