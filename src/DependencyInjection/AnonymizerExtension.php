<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class AnonymizerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        foreach (['faker', 'services'] as $value) {
            $loader->load(sprintf('%s.yaml', $value));
        }
    }
}
