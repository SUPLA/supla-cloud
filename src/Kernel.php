<?php

namespace App;

use App\DependencyInjection\SuplaExtension;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public const ROOT_PATH = __DIR__ . '/..';
    public const VAR_PATH = __DIR__ . '/../var';

    use MicroKernelTrait;

    protected function build(ContainerBuilder $container) {
        parent::build($container);
        $container->registerExtension(new SuplaExtension());
    }
}
