<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle\Tests;

use Nyholm\BundleTest\TestKernel;
use PHPSharkTank\Anonymizer\Anonymizer;
use PHPSharkTank\Anonymizer\AnonymizerInterface;
use PHPSharkTank\Anonymizer\ExclusionStrategy\ChainExclusionStrategy;
use PHPSharkTank\Anonymizer\ExclusionStrategy\ExpressionExclusionStrategy;
use PHPSharkTank\Anonymizer\Handler\FakerHandler;
use PHPSharkTank\Anonymizer\Loader\CachingLoader;
use PHPSharkTank\Anonymizer\Loader\ChainLoader;
use PHPSharkTank\Anonymizer\Registry\FakerHandlerRegistry;
use PHPSharkTank\AnonymizerBundle\AnonymizerBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        $kernel = parent::createKernel($options);
        self::assertInstanceOf(TestKernel::class, $kernel);

        $kernel->addTestBundle(AnonymizerBundle::class);
        $kernel->addTestCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                foreach ($container->getDefinitions() as $id => $definition) {
                    if (str_starts_with($id, 'sharktank_anonymizer')) {
                        $definition->setPublic(true);
                    }
                }

                foreach ($container->getAliases() as $id => $alias) {
                    if (str_starts_with($id, 'sharktank_anonymizer')) {
                        $alias->setPublic(true);
                    }
                }
            }
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testInitBundle(): void
    {
        $container = $this->bootWithConfig('minimal.yml');

        self::assertInstanceOf(Anonymizer::class, $container->get('sharktank_anonymizer.anonymizer'));
        self::assertInstanceOf(Anonymizer::class, $container->get('anonymizer'));
        self::assertInstanceOf(ChainLoader::class, $container->get('sharktank_anonymizer.mapping_loader'));
        self::assertInstanceOf(ChainExclusionStrategy::class, $container->get('sharktank_anonymizer.exclusion_strategy'));
        self::assertFalse($container->has('sharktank_anonymizer.handler.faker'));
        self::assertFalse($container->has('sharktank_anonymizer.exclusion_strategy.expression'));
    }

    public function testAutowiringAlias(): void
    {
        $container = $this->bootWithConfig('minimal.yml');

        self::assertSame($container->get('sharktank_anonymizer.anonymizer'), self::getContainer()->get(AnonymizerInterface::class));
    }

    public function testFaker(): void
    {
        $container = $this->bootWithConfig('faker.yml');

        self::assertInstanceOf(FakerHandler::class, $container->get('sharktank_anonymizer.handler.faker'));
        self::assertInstanceOf(FakerHandlerRegistry::class, $container->get('sharktank_anonymizer.handler_registry.faker'));
    }

    public function testWithCache(): void
    {
        $container = $this->bootWithConfig('cache.yml');

        self::assertInstanceOf(CachingLoader::class, $container->get('sharktank_anonymizer.mapping_loader'));
    }

    public function testExpressionExclusionStrategy(): void
    {
        $container = $this->bootWithConfig('expression_language.yml');

        self::assertInstanceOf(ExpressionExclusionStrategy::class, $container->get('sharktank_anonymizer.exclusion_strategy.expression'));
    }

    private function bootWithConfig(string $configFile): ContainerInterface
    {
        $kernel = self::bootKernel([
            'config' => static function (TestKernel $kernel) use ($configFile): void {
                $kernel->addTestConfig(__DIR__.'/Fixtures/config/'.$configFile);
            },
        ]);

        return $kernel->getContainer();
    }
}
