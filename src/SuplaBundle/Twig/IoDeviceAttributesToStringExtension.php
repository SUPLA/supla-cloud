<?php
namespace SuplaBundle\Twig;

use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Supla\SuplaConst;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IoDeviceAttributesToStringExtension extends \Twig_Extension {
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getFilters() {
        return [
            new \Twig_SimpleFilter('channelFunctionToString', [$this, 'channelFunctionToString']),
            new \Twig_SimpleFilter('actionExecutionResultToString', [$this, 'actionExecutionResultToString']),
        ];
    }

    public function channelFunctionToString($function) {
        $ioDeviceManager = $this->container->get('iodevice_manager');
        return $ioDeviceManager->channelFunctionToString($function);
    }

    public function actionExecutionResultToString($result) {
        $text = 'To be executed';
        if ($result === SuplaConst::ACTION_EXECUTION_RESULT_SUCCESS) $text = 'Successful';
        if ($result === SuplaConst::ACTION_EXECUTION_RESULT_DEVICE_UNREACHABLE) $text = 'Device was unreachable';
        if ($result === SuplaConst::ACTION_EXECUTION_RESULT_NO_SENSOR) $text = 'Disconnected sensor';
        if ($result === SuplaConst::ACTION_EXECUTION_RESULT_EXPIRED) $text = 'Expired';
        if ($result === SuplaConst::ACTION_EXECUTION_RESULT_ZOMBIE) $text = 'Failed (zombie)';
        if ($result === SuplaConst::ACTION_EXECUTION_RESULT_SERVER_UNREACHABLE) $text = 'Server unreachable';
        if ($result === SuplaConst::ACTION_EXECUTION_RESULT_FAILURE) $text = 'Failed';
        if ($result === SuplaConst::ACTION_EXECUTION_RESULT_CANCELLED) $text = 'Cancelled';
        return $this->container->get('translator')->trans($text);
    }

    public function getName() {
        return 'io_device_attributes_to_string';
    }
}
