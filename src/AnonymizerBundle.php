<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle;

use PHPSharkTank\Anonymizer\Handler\HandlerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AnonymizerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(HandlerInterface::class)
            ->addTag('phpsharktank.anonymizer.handler');
    }
}
