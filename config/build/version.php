<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $parameters = $container->parameters();

    $buildMetaFile = __DIR__ . '/version-meta.php';

    $build = is_file($buildMetaFile)
        ? require $buildMetaFile
        : [
            'supla.version' => 'dev',
            'supla.version_full' => 'dev',
        ];

    $parameters->set('supla.version', $build['supla.version']);
    $parameters->set('supla.version_full', $build['supla.version_full']);
};
