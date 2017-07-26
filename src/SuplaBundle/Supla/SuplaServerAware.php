<?php

namespace SuplaBundle\Supla;

trait SuplaServerAware {
    /** @var SuplaServer */
    protected $suplaServer;

    /** @required */
    public function setSuplaServer(SuplaServer $suplaServer): void {
        $this->suplaServer = $suplaServer;
    }
}
