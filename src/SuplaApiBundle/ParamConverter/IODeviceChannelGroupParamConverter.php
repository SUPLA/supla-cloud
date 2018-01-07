<?php
namespace SuplaApiBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\IODeviceChannelGroup;
use Symfony\Component\HttpFoundation\Request;

class IODeviceChannelGroupParamConverter implements ParamConverterInterface {
    use CurrentUserAware;

    public function apply(Request $request, ParamConverter $configuration) {
        $paramName = $configuration->getName();
        $user = $this->getCurrentUserOrThrow();
        $channelGroup = new IODeviceChannelGroup($user);
        $request->attributes->set($paramName, $channelGroup);
        return true;
    }

    public function supports(ParamConverter $configuration) {
        return $configuration->getClass() == IODeviceChannelGroup::class;
    }
}
