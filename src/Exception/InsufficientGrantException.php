<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Exception;

/**
 * The request relies on an existing consent (for example a verified bearer
 * token) but the user has no grant for the client, or the stored grant does
 * not cover all requested scopes. Password re-authentication is required;
 * no consent is created or expanded from the token alone.
 */
class InsufficientGrantException extends \RuntimeException {
}
