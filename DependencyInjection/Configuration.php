<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sharktank_anonymizer');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('enable_alias')->defaultTrue()->end()
                ->arrayNode('exclusion_strategy')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default')
                            ->canBeDisabled()
                        ->end()
                        ->arrayNode('expression')
                            ->canBeEnabled()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('pool')
                            ->defaultValue('system')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('faker')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('id')
                            ->defaultValue('faker')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('yaml_config')
                    ->prototype('scalar')
                    ->end()
                ->end()
        ->end();

        return $treeBuilder;
    }
}
