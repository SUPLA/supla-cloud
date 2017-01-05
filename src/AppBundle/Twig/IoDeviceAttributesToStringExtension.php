<?php
namespace AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class IoDeviceAttributesToStringExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('channelFunctionToString', array($this, 'channelFunctionToString')),
        ];
    }

    public function channelFunctionToString($function)
    {
        $ioDeviceManager = $this->container->get('iodevice_manager');
        return $ioDeviceManager->channelFunctionToString($function);
    }

    public function getName()
    {
        return 'io_device_attributes_to_string';
    }
}