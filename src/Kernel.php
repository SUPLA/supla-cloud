<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public const ROOT_PATH = __DIR__ . '/..';
    public const VAR_PATH = __DIR__ . '/../var';

    use MicroKernelTrait;
}
