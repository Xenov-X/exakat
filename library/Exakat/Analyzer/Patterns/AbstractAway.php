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

namespace Exakat\Analyzer\Patterns;

use Exakat\Analyzer\Analyzer;

class AbstractAway extends Analyzer {
    protected $abstractableCalls = array('\\time',
                                         '\\date',
                                         '\\rand',
                                         '\\mt_rand',
                                         '\\random_int',
                                         '\\random_byte',
                                         '\\getdate',
                                         '\\gettimeofday',
                                         '\\gmdate',
                                         '\\localtime',
                                         '\\microtime',
                                         );
    
    public function analyze() : void {
        $abstractableCalls = makeFullnspath($this->abstractableCalls);

        // $a = date();
        $this->atomFunctionIs($abstractableCalls)
                     // Must belong to a method
             ->hasInstruction(array('Method'))
             ->not(
                $this->side()
                     ->inIs('RIGHT')
                     ->atomIs('Assignation')

                    // must be returned by the method
                     ->outIs('LEFT')
                     ->atomIs('Variable')
                     ->inIs('DEFINITION')
                     ->outIs('DEFINITION')
                     ->hasIn('RETURNED')
             )

             // Not returned immediately
             ->hasNoIn('RETURN')
             ->back('first');
        $this->prepareQuery();

        // $a = date();
        $this->atomFunctionIs($abstractableCalls)
             // Must belong to a method
             ->hasNoInstruction(array('Method'))
             ->back('first');
        $this->prepareQuery();

    }
}

?>
