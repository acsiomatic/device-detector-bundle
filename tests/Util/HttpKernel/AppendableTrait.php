<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

trait AppendableTrait
{
    /**
     * @var array<BundleInterface>
     */
    private $appendedBundles = [];

    /**
     * @var array<string, mixed>
     */
    private $appendedExtensionConfigurations = [];

    /**
     * @var array<CompilerPassInterface>
     */
    private $appendedCompilersPass = [];

    /**
     * @return void
     */
    public function appendBundle(BundleInterface $bundle)
    {
        $this->appendedBundles[] = $bundle;
    }

    /**
     * @param array<mixed> $config
     *
     * @return void
     */
    public function appendExtensionConfiguration(string $extension, array $config = [])
    {
        $this->appendedExtensionConfigurations[$extension] = $config;
    }

    /**
     * @return void
     */
    public function appendCompilerPass(CompilerPassInterface $compilerPass)
    {
        $this->appendedCompilersPass[] = $compilerPass;
    }
}
