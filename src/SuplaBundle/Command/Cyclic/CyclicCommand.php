<?php
namespace SuplaBundle\Command\Cyclic;

interface CyclicCommand {
    public function getName();

    public function shouldRunNow(): bool;
}
