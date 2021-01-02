<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sharktank_anonymizer');

        $this->getRootNode($treeBuilder, 'sharktank_anonymizer')
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

    /**
     * Proxy to get root node for Symfony < 4.2.
     *
     * @return ArrayNodeDefinition
     */
    protected function getRootNode(TreeBuilder $treeBuilder, string $name)
    {
        if (\method_exists($treeBuilder, 'getRootNode')) {
            return $treeBuilder->getRootNode();
        } else {
            return $treeBuilder->root($name);
        }
    }
}
