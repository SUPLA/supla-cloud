<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel {
    const ROOT_PATH = __DIR__;
    const VAR_PATH = __DIR__ . '/../var';

    public function registerBundles() {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new SuplaBundle\SuplaBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new SuplaDeveloperBundle\SuplaDeveloperBundle();
        }

        return $bundles;
    }

    public function getRootDir() {
        return __DIR__;
    }

    public function getCacheDir() {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir() {
        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
        $optionalLocalConfigFile = $this->getRootDir() . '/config/config_local.yml';
        if (file_exists($optionalLocalConfigFile)) {
            $loader->load($optionalLocalConfigFile);
        }
        // optional webpack dev server: https://www.slideshare.net/nachomartin/webpacksf/60
        $loader->load(function ($container) {
            /** @var ContainerInterface $container */
            if ($this->getEnvironment() === 'dev' && $container->getParameter('use_webpack_dev_server')) {
                $protocol = $container->getParameter('supla_protocol');
                $container->loadFromExtension('framework', [
                    'assets' => [
                        'packages' => [
                            'webpack' => [
                                'base_urls' => [$protocol . '://localhost:25787'],
                            ],
                        ],
                    ],
                ]);
            }
        });
    }

    protected function build(\Symfony\Component\DependencyInjection\ContainerBuilder $container) {
        parent::build($container);
        if ($this->getEnvironment() === 'test') {
            $container->addCompilerPass(new SuplaBundle\Tests\Integration\TestContainerPass(), \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_OPTIMIZE);
        }
    }
}
