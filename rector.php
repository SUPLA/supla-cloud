<?php

return Rector\Config\RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpSets(php70: true);
