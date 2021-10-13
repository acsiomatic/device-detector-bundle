<?php

namespace Acsiomatic\DeviceDetectorBundle\Tests\Util\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use AppendableTrait;
    use TmpDirTrait;

    /**
     * @return array<BundleInterface>
     */
    public function registerBundles(): array
    {
        return $this->appendedBundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container): void {
            foreach ($this->appendedExtensionConfigurations as $extension => $config) {
                $container->loadFromExtension($extension, $config);
            }
        });
    }

    protected function build(ContainerBuilder $container): void
    {
        foreach ($this->appendedCompilersPass as $compilersPass) {
            $container->addCompilerPass($compilersPass);
        }
    }
}
