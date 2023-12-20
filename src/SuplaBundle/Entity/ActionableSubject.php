<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

interface ActionableSubject {
//    public function getId();

    public function getFunction(): ChannelFunction;

    /** @return ChannelFunctionAction[] */
    public function getPossibleActions(): array;

    /**
     * Returns a footprint of this functionable item for identification in SUPLA Server commands.
     * See SuplaServer#executeCommand for more details.
     */
    public function buildServerActionCommand(string $command, array $actionParams = []): string;

    /** Returns one of the ActionableSubjectType enum values. */
    public function getOwnSubjectType(): string;

    public function getUser(): User;
}
