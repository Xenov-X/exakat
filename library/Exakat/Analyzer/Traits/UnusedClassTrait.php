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

namespace Exakat\Analyzer\Traits;

use Exakat\Analyzer\Analyzer;

class UnusedClassTrait extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/MakeClassMethodDefinition',
                     'Complete/OverwrittenMethods',
                        
                    );
    }

    public function analyze(): void {
        // trait t { function t1() {}}
        // class x { use T; /* No call to $this->t1() */ }
        $this->atomIs(self::CLASSES_ALL)
             ->as('c')
             ->outIs('USE')
             ->outIs('USE')
             // No use of methods
             ->not(
                $this->side()
                     ->inIs('DEFINITION')
                     ->outIs(self::CLASS_METHODS)
                     ->as('r')

                     ->outIs('DEFINITION')
                     ->atomIs(array('Methodcall'))
                     ->goToClass(self::CLASSES_ALL)
                     ->raw('where( eq("c") )')
                )
             // No use of properties ?
                ->back('first');
        $this->prepareQuery();
    }
}

?>
