<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Enums\ChannelFunction;

interface ActionableSubject {
    public function getId(): int;

    public function getFunction(): ChannelFunction;

    /**
     * Returns a footprint of this functionable item for identification in SUPLA Server commands.
     * See SuplaServer#executeCommand for more details.
     */
    public function buildServerActionCommand(string $command, array $actionParams): string;

    /** Returns one of the ActionableSubjectType enum values. */
    public function getSubjectType(): string;

    public function getUser(): User;
}
