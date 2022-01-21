<?php

namespace SuplaBundle\Message;

interface BurningMessage {
    function burnAfterSeconds(): int;
}
