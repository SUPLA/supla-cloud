<?php

namespace SuplaBundle\Message;

interface BurningMessage {
    public function burnAfterSeconds(): int;
}
