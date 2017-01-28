<?php
namespace AppBundle\Twig;

use AppBundle\Model\IODeviceManager;
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
            new \Twig_SimpleFilter('functionActionToString', array($this, 'functionActionToString')),
        ];
    }

    public function channelFunctionToString($function)
    {
        $ioDeviceManager = $this->container->get('iodevice_manager');
        return $ioDeviceManager->channelFunctionToString($function);
    }

    public function functionActionToString($action)
    {
        /** @var IODeviceManager $ioDeviceManager */
        $ioDeviceManager = $this->container->get('iodevice_manager');
        $translator = $this->container->get('translator');
        $name = $ioDeviceManager->actionStringMap()[$action];
        return $translator->trans($name);
    }

    public function getName()
    {
        return 'io_device_attributes_to_string';
    }
}
