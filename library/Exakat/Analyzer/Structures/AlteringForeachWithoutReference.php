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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class AlteringForeachWithoutReference extends Analyzer {
    public function dependsOn() {
        return array('Arrays/IsModified',
                    );
    }
    
    public function analyze() {
        // foreach($a as $k => $v) { $a[$k] += 1;}
        $this->atomIs('Foreach')
             ->outIs('SOURCE')
             ->atomIs('Variable')
             ->savePropertyAs('code', 'source')
             ->inIs('SOURCE')

             ->outIs('VALUE')
             ->atomIs('Keyvalue')
             ->outIs('INDEX')
             ->savePropertyAs('code', 'k')
             ->inIs('INDEX')
             ->inIs('VALUE')

             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Array')
             ->raw('not( where( __.in("CAST").has("token", "T_UNSET_CAST") ) )' )
             ->raw('not( where( __.in("ARGUMENT").has("token", "T_UNSET") ) )' )
             ->analyzerIs('Arrays/IsModified')

             ->outIs('VARIABLE')
             ->samePropertyAs('code', 'source')
             ->inIs('VARIABLE')

             ->outIs('INDEX')
             ->samePropertyAs('code', 'k')

             ->back('first');
        $this->prepareQuery();
    }
}

?>
