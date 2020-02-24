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

namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class CompactInexistant extends Analyzer {
    public function dependsOn() : array {
        return array('Complete/CreateCompactVariables',
                    );
    }

    public function analyze() {
        // compact('a', 'b') with $b or $a that doesn't exists
        $this->atomFunctionIs('\\compact')
             ->outIs('ARGUMENT')
             ->as('results')
             ->has('noDelimiter')
             ->savePropertyAs('noDelimiter', 'variable_name')
             ->makeVariableName('variable_name')
             ->goToFunction()
             ->not(
                $this->side()
                     ->outIs('DEFINITION')
                     ->samePropertyAs('fullcode', 'variable_name', self::CASE_SENSITIVE)
             )
             ->not(
                $this->side()
                     ->outIs('ARGUMENT')
                     ->outIs('NAME')
                     ->samePropertyAs('fullcode', 'variable_name', self::CASE_SENSITIVE)
             )
             ->back('results');
        $this->prepareQuery();
    }
}

?>
