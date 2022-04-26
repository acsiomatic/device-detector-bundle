<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CallbackContainerPass implements CompilerPassInterface
{
    public function __construct(
        private readonly \Closure $callback,
    ) {}

    public function process(ContainerBuilder $container): void
    {
        \call_user_func($this->callback, $container);
    }
}
