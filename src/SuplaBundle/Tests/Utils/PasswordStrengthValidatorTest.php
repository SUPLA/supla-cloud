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

namespace SuplaBundle\Tests\Utils;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Utils\PasswordStrengthValidator;

class PasswordStrengthValidatorTest extends TestCase {
    /** @dataProvider examples */
    public function testValidators(PasswordStrengthValidator $validator, array $validPasswords, array $invalidPasswords) {
        foreach ($validPasswords as $validPassword) {
            $this->assertTrue($validator->isValid($validPassword), "Password: $validPassword");
        }
        foreach ($invalidPasswords as $invalidPassword) {
            $this->assertFalse($validator->isValid($invalidPassword), "Password: $invalidPassword");
        }
    }

    public static function examples() {
        return [
            [PasswordStrengthValidator::create()->requireNumbers(), ['1abc', 'abc1', '111', '0', ''], ['abc', 'rRr%', ' ']],
            [PasswordStrengthValidator::create()->requireLetters(), ['1abc', 'abc1', 'aaa', 'ąść', ''], ['111', '3#4%', ' ']],
            [PasswordStrengthValidator::create()->requireSpecialCharacters(), ['1a$bc', 'abc!1', '((', '    '], ['111', 'ąść']],
            [PasswordStrengthValidator::create()->requireCaseDiff(), ['Aa', 'ąŚ', 'Malinka'], ['111', 'ąść', 'aaa']],
            [PasswordStrengthValidator::create()->minLength(4), ['1234', '    ', 'abc1'], ['111', 'ąść', '']],
            [PasswordStrengthValidator::create()->maxLength(4), ['1234', '    ', 'a', '', 'ąśćð'], ['12345', 'ąśćaaa']],
            [
                PasswordStrengthValidator::create()
                    ->requireLetters()
                    ->requireNumbers()
                    ->requireCaseDiff()
                    ->requireSpecialCharacters()
                    ->minLength(8)
                    ->maxLength(32),
                ['Ziomala123#', 'xY!+u<s4\'Tb;.JY='],
                ['ziomala123#', 'xY!+u3', 'Ziomala123#Ziomala123#Ziomala123#Ziomala123#'],
            ],
        ];
    }
}
