<?php

namespace SuplaBundle\EventListener;

use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class FlushSuplaServerCommandsOnResponseListener {
    use SuplaServerAware;

    public function __invoke(ResponseEvent $event) {
        $this->suplaServer->flushPostponedCommands();
    }
}
