<?php
namespace SuplaApiBundle\ParamConverter;

use Assert\Assertion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;
use Symfony\Component\HttpFoundation\Request;

class IODeviceChannelGroupParamConverter implements ParamConverterInterface {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;
    /** @var IODeviceChannelRepository */
    private $channelRepository;

    public function __construct(LocationRepository $locationRepository, IODeviceChannelRepository $channelRepository) {
        $this->locationRepository = $locationRepository;
        $this->channelRepository = $channelRepository;
    }

    public function apply(Request $request, ParamConverter $configuration) {
        $paramName = $configuration->getName();
        if ($request->get($paramName)) {
            return false;
        }
        $user = $this->getCurrentUserOrThrow();
        $data = $request->request->all();
        $channelIds = $data['channelIds'] ?? [];
        Assertion::isArray($channelIds);
        Assertion::greaterThan(count($channelIds), 0, 'Channel group must consist of at least one channel.');
        $channels = array_map(function ($channelId) use ($user) {
            return $this->channelRepository->findForUser($user, $channelId);
        }, $channelIds);
        $channelGroup = new IODeviceChannelGroup($user, $user->getLocations()[0], $channels);
        $channelGroup->setCaption($data['caption'] ?? '');
        if (isset($data['hidden'])) {
            $channelGroup->setHidden(boolval($data['hidden']));
        }
        if (isset($data['enabled'])) {
            $channelGroup->setEnabled(boolval($data['enabled']));
        }
        $request->attributes->set($paramName, $channelGroup);
        return true;
    }

    public function supports(ParamConverter $configuration) {
        return $configuration->getClass() == IODeviceChannelGroup::class;
    }
}
