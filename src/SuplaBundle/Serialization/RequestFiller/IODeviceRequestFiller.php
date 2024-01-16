<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\UserConfigTranslator\IODeviceConfigTranslator;
use SuplaBundle\Repository\LocationRepository;

class IODeviceRequestFiller extends AbstractRequestFiller {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;
    /** @var IODeviceConfigTranslator */
    private $configTranslator;

    public function __construct(LocationRepository $locationRepository, IODeviceConfigTranslator $configTranslator) {
        $this->locationRepository = $locationRepository;
        $this->configTranslator = $configTranslator;
    }

    /** @param IODevice $device */
    public function fillFromData(array $data, $device = null) {
        Assertion::notNull($device);
        if (array_key_exists('enabled', $data)) {
            $enabled = filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN);
            $device->setEnabled($enabled);
        }
        if (array_key_exists('comment', $data)) {
            $comment = $data['comment'] ?: '';
            Assertion::string($comment);
            Assertion::maxLength($comment, 200, 'Caption is too long.'); // i18n
            $device->setComment($comment);
        }
        if (array_key_exists('config', $data)) {
            Assertion::isArray($data['config']);
            $this->configTranslator->setConfig($device, $data['config']);
        }
        if (array_key_exists('locationId', $data)) {
            $user = $this->getCurrentUserOrThrow();
            $location = $this->locationRepository->findForUser($user, $data['locationId']);
            $device->setLocation($location);
        }
        return $device;
    }
}
