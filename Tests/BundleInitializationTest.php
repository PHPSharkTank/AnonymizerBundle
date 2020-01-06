<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle\Tests;

use Faker\Generator;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use PHPSharkTank\Anonymizer\Anonymizer;
use PHPSharkTank\Anonymizer\ExclusionStrategy\DefaultExclusionStrategy;
use PHPSharkTank\Anonymizer\ExclusionStrategy\ExpressionExclusionStrategy;
use PHPSharkTank\Anonymizer\Handler\FakerHandler;
use PHPSharkTank\Anonymizer\Loader\CachingLoader;
use PHPSharkTank\Anonymizer\Registry\FakerHandlerRegistry;
use PHPSharkTank\AnonymizerBundle\AnonymizerBundle;
use Symfony\Bridge\PhpUnit\SetUpTearDownTrait;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class BundleInitializationTest extends BaseBundleTestCase
{
    use SetUpTearDownTrait;

    protected function doSetUp(): void
    {
        $this->addCompilerPass(new PublicServicePass('|^sharktank_anonymizer*|'));
    }

    protected function getBundleClass()
    {
        return AnonymizerBundle::class;
    }

    protected function createKernel()
    {
        $kernel = parent::createKernel();
        $kernel->addConfigFile(__DIR__.'/Fixtures/config/minimal.yml');

        return $kernel;
    }

    public function testInitBundle()
    {
        $this->bootKernel();

        $container = $this->getContainer();

        self::assertInstanceOf(Anonymizer::class, $container->get('sharktank_anonymizer.anonymizer'));
        self::assertInstanceOf(Anonymizer::class, $container->get('anonymizer'));

        self::assertInstanceOf(DefaultExclusionStrategy::class, $container->get('sharktank_anonymizer.exclusion_strategy.default'));
    }

    public function testFaker()
    {
        if (!class_exists(Generator::class)) {
            self::markTestSkipped('Faker library hasn\'t be installed!');
        }

        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/Fixtures/config/faker.yml');
        $this->bootKernel();

        $container = $this->getContainer();

        self::assertTrue($container->has('sharktank_anonymizer.handler.faker'));
        self::assertTrue($container->has('sharktank_anonymizer.handler_registry.faker'));

        self::assertInstanceOf(FakerHandler::class, $container->get('sharktank_anonymizer.handler.faker'));
        self::assertInstanceOf(FakerHandlerRegistry::class, $container->get('sharktank_anonymizer.handler_registry.faker'));
    }

    public function testWithCache()
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/Fixtures/config/cache.yml');
        $this->bootKernel();

        $container = $this->getContainer();

        self::assertInstanceOf(CachingLoader::class, $container->get('sharktank_anonymizer.mapping_loader'));
    }

    public function testExpressionVisitors()
    {
        if (!class_exists(ExpressionLanguage::class)) {
            self::markTestSkipped('The "symfony/expression-language" has not been installed.');
        }

        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/Fixtures/config/expression_language.yml');
        $this->bootKernel();

        $container = $this->getContainer();

        self::assertInstanceOf(ExpressionExclusionStrategy::class, $container->get('sharktank_anonymizer.exclusion_strategy.expression'));
    }
}
