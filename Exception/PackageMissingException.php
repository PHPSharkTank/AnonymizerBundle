<?php

declare(strict_types=1);

namespace PHPSharkTank\AnonymizerBundle\Exception;

use Throwable;

class PackageMissingException extends \Exception
{
    public function __construct(string $package)
    {
        parent::__construct(sprintf(
            'The package "%1$s" is missing. Please run "composer require %1$s".',
            $package
        ));
    }
}
