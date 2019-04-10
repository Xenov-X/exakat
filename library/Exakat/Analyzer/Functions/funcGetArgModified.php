<?php
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;

class funcGetArgModified extends Analyzer {
    public function analyze() {
        // function foo($a = 3) { $args = func_get_args(); $a++; }
        $this->atomIs(self::$FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->outIsIE('RIGHT')
             ->savePropertyAs('rank', 'ranked')
             ->savePropertyAs('code', 'arg')
             ->inIsIE('RIGHT')
             ->inIs('ARGUMENT')
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Functioncall')
             ->functioncallIs('\\func_get_arg')
             ->outIs('ARGUMENT')
             ->atomIs('Integer')
             ->isNot('intval', null)
             ->samePropertyAs('intval', 'ranked', self::CASE_SENSITIVE)
             ->goToFunction()
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Variable')
             ->samePropertyAs('code', 'arg')
             ->is('isModified', true)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
