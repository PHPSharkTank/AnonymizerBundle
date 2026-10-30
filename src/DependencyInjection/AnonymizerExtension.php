<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle\DependencyInjection;

use PHPSharkTank\Anonymizer\AnonymizerInterface;
use PHPSharkTank\Anonymizer\ExclusionStrategy\ExpressionExclusionStrategy;
use PHPSharkTank\Anonymizer\Handler\HandlerInterface;
use PHPSharkTank\Anonymizer\Loader\CachingLoader;
use PHPSharkTank\Anonymizer\Registry\HandlerRegistryInterface;
use PHPSharkTank\AnonymizerBundle\Exception\PackageMissingException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class AnonymizerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $this->loadMappingDrivers($loader);
        $this->loadExclusionStrategies($this->arrayNode($config, 'exclusion_strategy'), $container);

        if ($config['enable_alias']) {
            $container->setAlias('anonymizer', 'sharktank_anonymizer.anonymizer')->setPublic(true);
        }

        $fakerConfig = $this->arrayNode($config, 'faker');
        if ($this->isConfigEnabled($container, $fakerConfig)) {
            $loader->load('faker.yaml');
            $container->setAlias('sharktank_anonymizer.faker', $this->stringNode($fakerConfig, 'id'));
        }

        $yamlConfig = $this->arrayNode($config, 'yaml_config');
        if ($yamlConfig) {
            $yamlLoaderDefinition = $container->findDefinition('sharktank_anonymizer.mapping.yaml_loader');
            foreach ($yamlConfig as $path) {
                $yamlLoaderDefinition->addMethodCall('addYamlPath', [$path]);
            }
        }

        $cacheConfig = $this->arrayNode($config, 'cache');
        if ($this->isConfigEnabled($container, $cacheConfig)) {
            $id = (string) $container->getAlias('sharktank_anonymizer.mapping_loader');
            $cacheDefinition = new Definition(CachingLoader::class);

            $cacheDefinition->setDecoratedService($id);
            $cacheDefinition->setArguments([
                new Reference('sharktank_anonymizer.mapping_loader.inner'),
                new Reference(\sprintf('cache.%s', $this->stringNode($cacheConfig, 'pool'))),
            ]);

            $container->setDefinition('sharktank_anonymizer.mapping_loader', $cacheDefinition);
        }

        $container->registerForAutoconfiguration(HandlerInterface::class)
            ->addTag('sharktank_anonymizer.handler');
        $container->registerForAutoconfiguration(HandlerRegistryInterface::class)
            ->addTag('sharktank_anonymizer.handler_registry');

        $container->setAlias(AnonymizerInterface::class, 'sharktank_anonymizer.anonymizer');
    }

    /**
     * @param array<mixed> $config
     */
    private function loadExclusionStrategies(array $config, ContainerBuilder $container): void
    {
        if ($this->isConfigEnabled($container, $this->arrayNode($config, 'expression'))) {
            if (!class_exists(ExpressionLanguage::class)) {
                throw new PackageMissingException('symfony/expression-language');
            }

            $definition = new Definition(ExpressionLanguage::class);
            $container->setDefinition('sharktank_anonymizer.exclusion_strategy.expression_language', $definition);

            $definition = new Definition(ExpressionExclusionStrategy::class, [
                new Reference('sharktank_anonymizer.exclusion_strategy.expression_language'),
            ]);
            $definition->addTag('sharktank_anonymizer.exclusion_strategy');

            $container->setDefinition('sharktank_anonymizer.exclusion_strategy.expression', $definition);
        }
    }

    /**
     * @param array<mixed> $config
     *
     * @return array<mixed>
     */
    private function arrayNode(array $config, string $key): array
    {
        $value = $config[$key] ?? null;

        if (!\is_array($value)) {
            throw new \LogicException(\sprintf('The processed "%s" configuration must be an array.', $key));
        }

        return $value;
    }

    /**
     * @param array<mixed> $config
     */
    private function stringNode(array $config, string $key): string
    {
        $value = $config[$key] ?? null;

        if (!\is_string($value)) {
            throw new \LogicException(\sprintf('The processed "%s" configuration must be a string.', $key));
        }

        return $value;
    }

    private function loadMappingDrivers(YamlFileLoader $loader): void
    {
        $loader->load('annotation.yaml');
    }

    public function getAlias(): string
    {
        return 'sharktank_anonymizer';
    }
}
