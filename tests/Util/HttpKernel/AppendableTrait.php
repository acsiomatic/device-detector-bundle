<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

trait AppendableTrait
{
    /**
     * @var array<BundleInterface>
     */
    private $appendedBundles = [];

    /**
     * @var array<string, array<mixed>>
     */
    private $appendedExtensionConfigurations = [];

    /**
     * @var array<CompilerPassInterface>
     */
    private $appendedCompilersPass = [];

    /**
     * @var array<string, Definition>
     */
    private $appendDefinitions = [];

    public function appendBundle(BundleInterface $bundle): void
    {
        $this->appendedBundles[] = $bundle;
    }

    /**
     * @param array<mixed> $config
     */
    public function appendExtensionConfiguration(string $extension, array $config = []): void
    {
        $this->appendedExtensionConfigurations[$extension] = $config;
    }

    public function appendCompilerPass(CompilerPassInterface $compilerPass): void
    {
        $this->appendedCompilersPass[] = $compilerPass;
    }

    public function appendDefinition(string $id, Definition $definition): void
    {
        $this->appendDefinitions[$id] = $definition;
    }
}
