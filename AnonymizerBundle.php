<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle;

use PHPSharkTank\AnonymizerBundle\DependencyInjection\AnonymizerExtension;
use PHPSharkTank\AnonymizerBundle\DependencyInjection\CompilerPass\ExclusionStrategyCompilerPass;
use PHPSharkTank\AnonymizerBundle\DependencyInjection\CompilerPass\HandlerCompilerPass;
use PHPSharkTank\AnonymizerBundle\DependencyInjection\CompilerPass\HandlerRegistryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AnonymizerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new HandlerCompilerPass());
        $container->addCompilerPass(new HandlerRegistryCompilerPass());
        $container->addCompilerPass(new ExclusionStrategyCompilerPass());
    }

    public function getContainerExtension()
    {
        return new AnonymizerExtension();
    }
}
