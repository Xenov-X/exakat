<?php
/*
 * Copyright 2012-2018 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

namespace Exakat\Analyzer\Wordpress;

use Exakat\Analyzer\Analyzer;

class UnescapedVariables extends Analyzer {
    public function analyze() {
        $escFunctions = $this->loadIni('wordpress_functions.ini', 'esc');
        $escFunctions = makeFullNsPath($escFunctions);
        
        // echo esc_attr($a) . $unescapedVar
        $this->atomIs(array('Echo', 'Print'))
             ->outIs('ARGUMENT')
             ->outIsIE('CODE')
             ->atomIs(array('Variable', 'Phpvariable', 'Array', 'Member', 'Staticproperty', 'Concatenation'))
             ->outIsIE('CONCAT')
             ->atomIs(array('Variable', 'Phpvariable', 'Array', 'Member', 'Staticproperty'))
             ->back('first');
        $this->prepareQuery();

        // echo esc_attr($a) . $unescapedVar
        $this->atomIs(array('Echo', 'Print'))
             ->outIs('ARGUMENT')
             ->outIsIE('CODE')
             ->outIsIE('CONCAT')
             ->atomIs('Functioncall')
             ->has('fullnspath')
             ->fullnspathIsNot($escFunctions)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
