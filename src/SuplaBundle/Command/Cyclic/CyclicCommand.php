<?php
namespace SuplaBundle\Command\Cyclic;

use SuplaBundle\Model\TimeProvider;

interface CyclicCommand {
    public function getName();

    public function shouldRunNow(TimeProvider $timeProvider): bool;
}
