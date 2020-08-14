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

namespace Exakat\Analyzer\Typehints;

use Exakat\Analyzer\Analyzer;

class CouldBeSelf extends Analyzer {
    public function analyze(): void {
        // This analysis works on typehints already available

        // Properties
        $this->atomIs('Ppp')
             ->outIs('TYPEHINT')
             ->atomIsNot('Self')
             ->inIs('DEFINITION')
             ->outIs('PPP')
             ->identicalTo('first');
        $this->prepareQuery();

        // Properties, parameter
        $this->atomIs(array('Ppp', 'Parameter'))
             ->outIs('TYPEHINT')
             ->atomIsNot('Self')
             ->inIs('DEFINITION')
             ->outIs(array('MAGICMETHOD', 'METHOD'))
             ->outIs('ARGUMENT')
             ->identicalTo('first');
        $this->prepareQuery();

        // return types
        $this->atomIs('Method')
             ->outIs('RETURNTYPE')
             ->atomIsNot('Self')
             ->inIs('DEFINITION')
             ->outIs(array('MAGICMETHOD', 'METHOD'))
             ->identicalTo('first');
        $this->prepareQuery();
    }
}

?>
