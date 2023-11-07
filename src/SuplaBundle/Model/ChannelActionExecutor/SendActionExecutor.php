<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Repository\AccessIdRepository;

/**
 * @OA\Schema(schema="ChannelActionParamsSend",
 *     description="Action params for `SEND` action.",
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="body", type="string"),
 *     @OA\Property(property="accessIds", type="array", @OA\Items(type="integer")),
 * )
 */
class SendActionExecutor extends SingleChannelActionExecutor {

    /**
     * @var AccessIdRepository
     */
    private $accessIdRepository;

    public function __construct(AccessIdRepository $accessIdRepository) {
        $this->accessIdRepository = $accessIdRepository;
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SEND();
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::NOTIFICATION(),
        ];
    }

    /** @param PushNotification $notification */
    public function execute(ActionableSubject $notification, array $actionParams = []) {
        $payload = [
            'title' => $actionParams['title'] ?? '',
            'body' => $actionParams['body'],
            'recipients' => [
                'aids' => $actionParams['accessIds'],
            ],
        ];
        $command = $notification->buildServerActionCommand('SEND-PUSH', $payload);
        $this->suplaServer->executeCommand($command);
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $notification, array $actionParams): array {
        $actionParams = array_intersect_key($actionParams, array_flip(['title', 'body', 'accessIds']));
        if (isset($actionParams['title'])) {
            Assertion::string($actionParams['title']);
        }
        Assertion::keyExists($actionParams, 'body', 'Notification must have a body.');
        Assertion::string($actionParams['body']);
        Assertion::notBlank($actionParams['body'], 'Notification must have a body.');
        Assertion::keyExists($actionParams, 'accessIds', 'Notification must have recipients.');
        Assertion::isArray($actionParams['accessIds'], 'Notification must have recipients.');
        $actionParams['accessIds'] = array_map(function ($aid) use ($notification) {
            $aid = intval(is_array($aid) ? ($aid['id'] ?? 0) : $aid);
            $this->accessIdRepository->findForUser($notification->getUser(), $aid);
            return $aid;
        }, $actionParams['accessIds']);
        return $actionParams;
    }
}
