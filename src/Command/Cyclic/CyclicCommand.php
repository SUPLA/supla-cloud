<?php
namespace App\Command\Cyclic;

use App\Model\TimeProvider;

interface CyclicCommand {
    public function getName();

    public function shouldRunNow(TimeProvider $timeProvider): bool;
}
