<?php
/*
 * Copyright 2012-2018 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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

class AddDefaultValue extends Analyzer {
    public function analyze() {
        // function foo($x) { ...; $x = 0; ...}
        $this->atomIs(self::$FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->isNot('reference', true)
             ->isNot('variadic', true)
             ->hasNoOut('DEFAULT')
             ->outIs('NAME')
             ->outIs('DEFINITION')
             ->inIs('LEFT')
             ->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('RIGHT')
             ->is('constant', true)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
