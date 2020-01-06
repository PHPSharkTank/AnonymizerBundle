<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ExclusionStrategyCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('sharktank_anonymizer.exclusion_strategy')) {
            return;
        }

        $arguments = [];
        $definition = $container->findDefinition('sharktank_anonymizer.exclusion_strategy');

        foreach ($container->findTaggedServiceIds('sharktank_anonymizer.exclusion_strategy') as $id => $tags) {
            $arguments[] = new Reference($id);
        }

        $definition->replaceArgument(0, $arguments);
    }

}
