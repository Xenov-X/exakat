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

class UselessDefault extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/PropagateCalls',
                     'Complete/FollowClosureDefinition',
                    );
    }

    public function analyze() {
        // function foo($a = 1)
        // foo(1); foo(2); foo(3); // always provide the arg
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->savePropertyAs('rank', 'ranked')
             ->outIs('DEFAULT')
             ->atomIsNot('Void')
             ->hasNoIn('LEFT')
             ->back('first')
             // at lease 2 usage of the method call. 
             ->filter(
                $this->side()
                     ->outIs('DEFINITION')
                     ->raw('count().is(gt(2))')
             )
             ->not(
                $this->side()
                     ->outIs('DEFINITION')
                     ->outIsIE('METHOD')
                     ->noChildWithRank('ARGUMENT', 'ranked')
             )
             ->back('first');
        $this->prepareQuery();
    }
}

?>
