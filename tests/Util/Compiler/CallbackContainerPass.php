<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests\Util\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CallbackContainerPass implements CompilerPassInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        \call_user_func($this->callback, $container);
    }
}
