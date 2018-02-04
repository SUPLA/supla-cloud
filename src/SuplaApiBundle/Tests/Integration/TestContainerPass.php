<?php
namespace SuplaApiBundle\Tests\Integration;

use Assert\Assertion;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Makes enumerated private services that needs to be tested public so they can be fetched from the container without a deprecation warning.
 *
 * @see https://github.com/symfony/symfony-docs/issues/8097
 * @see https://github.com/symfony/symfony/issues/24543
 */
class TestContainerPass implements CompilerPassInterface {
    private static $publicInTests = [
        \SuplaApiBundle\ParamConverter\ChannelParamsUpdater\ChannelParamsUpdater::class,
    ];

    public function process(ContainerBuilder $container) {
        $madePublic = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            if (in_array($id, self::$publicInTests, true) || in_array($definition->getClass(), self::$publicInTests, true)) {
                $definition->setPublic(true);
                $madePublic[] = $id;
            }
        }
        foreach ($container->getAliases() as $id => $definition) {
            if (in_array($id, self::$publicInTests, true)) {
                $definition->setPublic(true);
                $madePublic[] = $id;
            }
        }
        Assertion::count($madePublic, count(self::$publicInTests), function () use ($madePublic) {
            return 'The following services were not made public although they have been requested: '
                . implode(', ', array_diff(self::$publicInTests, $madePublic));
        });
    }
}
