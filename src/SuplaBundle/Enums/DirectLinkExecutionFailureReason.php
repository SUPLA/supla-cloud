<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static DirectLinkExecutionFailureReason DISABLED()
 * @method static DirectLinkExecutionFailureReason NOT_ACTIVE_YET()
 * @method static DirectLinkExecutionFailureReason EXPIRED()
 * @method static DirectLinkExecutionFailureReason EXECUTION_LIMIT_EXCEEDED()
 * @method static DirectLinkExecutionFailureReason NO_ALLOWED_ACTIONS()
 * @method static DirectLinkExecutionFailureReason HTTP_GET_FORBIDDEN()
 * @method static DirectLinkExecutionFailureReason NO_SLUG_OR_ACTION()
 * @method static DirectLinkExecutionFailureReason UNSUPPORTED_ACTION()
 * @method static DirectLinkExecutionFailureReason FORBIDDEN_ACTION()
 * @method static DirectLinkExecutionFailureReason INVALID_SLUG()
 * @method static DirectLinkExecutionFailureReason INVALID_ACTION_PARAMETERS()
 * @method static DirectLinkExecutionFailureReason OTHER_FAILURE()
 */
final class DirectLinkExecutionFailureReason extends Enum {
    const DISABLED = 'directLinkExecutionFailureReason_disabled'; // i18n
    const NOT_ACTIVE_YET = 'directLinkExecutionFailureReason_notActiveYet'; // i18n
    const EXPIRED = 'directLinkExecutionFailureReason_expired'; // i18n
    const EXECUTION_LIMIT_EXCEEDED = 'directLinkExecutionFailureReason_executionLimitExceeded'; // i18n
    const NO_ALLOWED_ACTIONS = 'directLinkExecutionFailureReason_noAllowedActions'; // i18n
    const HTTP_GET_FORBIDDEN = 'directLinkExecutionFailureReason_httpGetForbidden'; // i18n
    const NO_SLUG_OR_ACTION = 'directLinkExecutionFailureReason_noSlugOrAction'; // i18n
    const UNSUPPORTED_ACTION = 'directLinkExecutionFailureReason_unsupportedAction'; // i18n
    const FORBIDDEN_ACTION = 'directLinkExecutionFailureReason_forbiddenAction'; // i18n
    const INVALID_SLUG = 'directLinkExecutionFailureReason_invalidSlug'; // i18n
    const INVALID_ACTION_PARAMETERS = 'directLinkExecutionFailureReason_invalidActionParameters'; // i18n
    const OTHER_FAILURE = 'directLinkExecutionFailureReason_otherFailure'; // i18n

    public function getLabel(): string {
        return self::getLabels()[$this->getValue()];
    }

    public static function getLabels(): array {
        return [
            self::DISABLED => 'Direct link is disabled.',
            self::NOT_ACTIVE_YET => 'Direct link is not active yet.',
            self::EXPIRED => 'Direct link has expired.',
            self::EXECUTION_LIMIT_EXCEEDED => 'Execution limit has been exceeded.',
            self::NO_ALLOWED_ACTIONS => 'No allowed actions have been chosen.',
            self::HTTP_GET_FORBIDDEN => 'The action was prevented from being performed using an HTTP GET method that is not permitted.',
            self::NO_SLUG_OR_ACTION => 'Invalid request data: code and action required.',
            self::UNSUPPORTED_ACTION => 'Requested action is not supported.',
            self::FORBIDDEN_ACTION => 'The requested action is not allowed for this direct link.',
            self::INVALID_SLUG => 'Given verification code is invalid.',
            self::INVALID_ACTION_PARAMETERS => 'Invalid action parameters.',
            self::OTHER_FAILURE => 'Direct link execution failure.',
        ];
    }
}
