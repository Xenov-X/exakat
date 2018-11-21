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

namespace Exakat\Analyzer\Slim;

use Exakat\Analyzer\Common\Slim;

class UsedRoutes extends Slim {
    public function analyze() {
        // Collect variables that store application
        $apps = $this->getAppVariables();

        // didn't find any application variable. Quit.
        if (empty($apps)) {
            return;
        }
        
        // Callable is a closure
        $this->atomIs('Methodcall')
             ->outIs('OBJECT')
             ->atomIs('Variableobject')
             ->codeIs($apps, self ::NO_TRANSLATE)
             ->back('first')
             ->outIs('METHOD')
             ->codeIs(array('get', 'put', 'any', 'patch', 'option', 'delete', 'post'))
             ->outWithRank('ARGUMENT', 0)
             ->atomIsNot('Void');
        $this->prepareQuery();
    }
}

?>
