<?php

return Rector\Config\RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpSets(php72: true);
