<?php declare(strict_types = 1);
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

class NoLiteralForReference extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/SetClassMethodRemoteDefinition',
                     'Complete/PropagateConstants',
                    );
    }

    public function analyze() {
        $atoms = array('Integer', 
                       'Null', 
                       'Void', 
                       'Float', 
                       'Addition', 
                       'Multiplication', 
                       'Bitshift', 
                       'Logical', 
                       'Ternary', 
                       'Identifier', 
                       'Nsname', 
                       'Assignation',
                       'Ternary',
                       );

        // foo(1)
        // function foo(&$r) {}
        $this->atomIs(self::CALLS)
             ->outIs('ARGUMENT')
             ->is('constant', true)
             ->atomIsNot(array('Void', 'Functioncall', 'Methodcall', 'Staticmethodcall'))
             ->savePropertyAs('rank', 'ranked')
             ->back('first')
             ->inIs('DEFINITION')
             ->outWithRank('ARGUMENT', 'ranked')
             ->is('reference', true)
             ->back('first');
        $this->prepareQuery();

        // foo(bar_without_reference)
        // function foo(&$r) {}
        $this->atomIs(self::CALLS)
             ->outIs('ARGUMENT')
             ->savePropertyAs('rank', 'ranked')
             ->atomIs(array('Functioncall', 'Methodcall', 'Staticmethodcall'))
             ->inIs('DEFINITION')
             ->isNot('reference', true)

             ->back('first')
             ->inIs('DEFINITION')
             ->outWithRank('ARGUMENT', 'ranked')
             ->is('reference', true)
             ->back('first');
        $this->prepareQuery();

        // function &foo($r) { return 2; }
        $this->atomIs(self::FUNCTIONS_ALL)
             ->is('reference', true)
             ->outIs('BLOCK')
             ->atomInside('Return')
             ->outIs('RETURN')
             ->outIsIE('CODE') // Skip parenthesis
             ->atomIs($atoms)
             ->back('first');
        $this->prepareQuery();

        // function &foo($r) { return foo_without_ref(); }
        $this->atomIs(self::FUNCTIONS_ALL)
             ->is('reference', true)
             ->outIs('BLOCK')
             ->atomInside('Return')
             ->outIs('RETURN')
             ->outIsIE('CODE') // Skip parenthesis
             ->atomIs(self::CALLS)
             ->inIs('DEFINITION')
             ->isNot('reference', true)
             ->outIs('RETURNTYPE')
             ->atomIs('Scalartypehint')
             ->back('first');
        $this->prepareQuery();

        // function &foo($r) { return foo_without_ref(); }
        $this->atomIs(self::FUNCTIONS_ALL)
             ->is('reference', true)
             ->outIs('BLOCK')
             ->atomInside('Return')
             ->outIs('RETURN')
             ->outIsIE('CODE') // Skip parenthesis
             ->atomIs(self::CALLS)
             ->inIs('DEFINITION')
             ->isNot('reference', true)
             ->outIs('RETURNTYPE')
             ->atomIs('Void')
             ->inIs('RETURNTYPE')
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Return')
             ->atomIsNot(self::CONTAINERS)
             ->back('first');
        $this->prepareQuery();

        // fn &foo($r) { return 2; }
        $this->atomIs('Arrowfunction')
             ->is('reference', true)
             ->outIs('BLOCK')
             ->outIsIE('CODE') // Skip parenthesis
             ->atomIs($atoms)
             ->back('first');
        $this->prepareQuery();

        // fn &foo($r) { return foo_without_ref(); }
        $this->atomIs('Arrowfunction')
             ->is('reference', true)
             ->outIs('BLOCK')
             ->outIsIE('CODE') // Skip parenthesis
             ->atomIs(self::CALLS)
             ->inIs('DEFINITION')
             ->isNot('reference', true)
             ->outIs('RETURNTYPE')
             ->atomIs('Scalartypehint')
             ->back('first');
        $this->prepareQuery();

        // function &foo($r) { return foo_without_ref(); }
        $this->atomIs('Arrowfunction')
             ->is('reference', true)
             ->outIs('BLOCK')
             ->outIsIE('CODE') // Skip parenthesis
             ->atomIs(self::CALLS)
             ->inIs('DEFINITION')
             ->isNot('reference', true)
             ->outIs('RETURNTYPE')
             ->atomIs('Void')
             ->inIs('RETURNTYPE')
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Return')
             ->atomIsNot(self::CONTAINERS)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
