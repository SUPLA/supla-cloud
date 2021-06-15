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

namespace SuplaBundle\Utils;

use Assert\Assertion;
use Assert\InvalidArgumentException;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Exception\ApiExceptionWithDetails;

final class PasswordStrengthValidator {
    private $minLength;
    private $maxLength;
    private $requireLetters = false;
    private $requireCaseDiff = false;
    private $requireNumbers = false;
    private $requireSpecialCharacters = false;

    public static function create() {
        return new self();
    }

    public function minLength(int $minLength = 8): self {
        $this->minLength = $minLength;
        return $this;
    }

    public function maxLength(int $maxLength = 32): self {
        $this->maxLength = $maxLength;
        return $this;
    }

    public function requireLetters(bool $requireLetters = true): self {
        $this->requireLetters = $requireLetters;
        return $this;
    }

    public function requireCaseDiff(bool $requireCaseDiff = true): self {
        $this->requireCaseDiff = $requireCaseDiff;
        return $this;
    }

    public function requireNumbers(bool $requireNumbers = true): self {
        $this->requireNumbers = $requireNumbers;
        return $this;
    }

    public function requireSpecialCharacters(bool $requireSpecialCharacters = true): self {
        $this->requireSpecialCharacters = $requireSpecialCharacters;
        return $this;
    }

    public function validate(string $rawPassword) {
        if (!$this->minLength && (null === $rawPassword || '' === $rawPassword)) {
            return;
        }
        if ($this->minLength && mb_strlen($rawPassword) < $this->minLength) {
            $msg = 'Password must be at least {minLength} characters.'; // i18n
            throw new ApiExceptionWithDetails($msg, ['minLength' => $this->minLength]);
        }
        if ($this->maxLength && mb_strlen($rawPassword) > $this->maxLength) {
            $msg = 'Password must be no longer than {maxLength} characters.'; // i18n
            throw new ApiExceptionWithDetails($msg, ['maxLength' => $this->maxLength]);
        }
        Assertion::true(
            !$this->requireLetters || preg_match('/\pL/u', $rawPassword),
            'Your password must include at least one letter.' // i18n
        );
        Assertion::true(
            !$this->requireCaseDiff || preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $rawPassword),
            'Your password must include both upper and lower case letters.' // i18n
        );
        Assertion::true(
            !$this->requireNumbers || preg_match('/\pN/u', $rawPassword),
            'Your password must include at least one number.' // i18n
        );
        Assertion::true(
            !$this->requireSpecialCharacters || preg_match('/[^p{Ll}\p{Lu}\pL\pN]/u', $rawPassword),
            'Your password must contain at least one special character.' // i18n
        );
    }

    public function isValid(string $invalidPassword): bool {
        try {
            $this->validate($invalidPassword);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        } catch (ApiException $e) {
            return false;
        }
    }
}
