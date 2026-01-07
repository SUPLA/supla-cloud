<?php

return Rector\Config\RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/vendor/dragonmantank/cron-expression/src',
    ])
    ->withPhpVersion(Rector\ValueObject\PhpVersion::PHP_82)
    ->withRules([
        Rector\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector::class,
        Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector::class,
    ]);
