<?php

return Rector\Config\RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpVersion(Rector\ValueObject\PhpVersion::PHP_84)
    ->withRules([
        Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector::class,
    ]);
