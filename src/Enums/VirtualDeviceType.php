<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Enums;

use MyCLabs\Enum\Enum;

final class VirtualDeviceType extends Enum {
    const DATA_SOURCE = 'DATA_SOURCE';
    const OCPP = 'OCPP';
}
