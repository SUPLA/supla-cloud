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

namespace SuplaBundle\Model\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Support for supla_is_now_active function.
 */
class IsNowActive extends FunctionNode {
    public $activeFrom = null;
    public $activeTo = null;
    public $activeHours = null;
    public $timezone = null;

    public function getSql(SqlWalker $sqlWalker) {
        return sprintf(
            'supla_is_now_active(%s, %s, %s, %s)',
            $this->activeFrom->dispatch($sqlWalker),
            $this->activeTo->dispatch($sqlWalker),
            $this->activeHours->dispatch($sqlWalker),
            $this->timezone->dispatch($sqlWalker)
        );
    }

    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->activeFrom = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->activeTo = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->activeHours = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->timezone = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
