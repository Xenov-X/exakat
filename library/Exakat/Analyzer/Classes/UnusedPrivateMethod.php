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


namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class UnusedPrivateMethod extends Analyzer {
    public function dependsOn(): array {
        return array('Classes/UsedPrivateMethod',
                     'Classes/DynamicSelfCalls',
                    );
    }

    public function analyze(): void {
        // class X { private function foo() { } }
        $this->atomIs(self::CLASSES_ALL)
             ->analyzerIsNot('Classes/DynamicSelfCalls')
             ->outIs('METHOD')
             ->atomIs('Method')
             ->is('visibility', 'private')
             ->analyzerIsNot('Classes/UsedPrivateMethod');
        $this->prepareQuery();

        // class X { protected function foo() { } }
        // No extend, no extension
        $this->atomIs(self::CLASSES_ALL)
             ->hasNoOut('EXTENDS')
             ->not(
                $this->side()
                     ->outIs('DEFINITION')
                     ->inIs('EXTENDS')
                     ->atomis('Class')
             )
             ->analyzerIsNot('Classes/DynamicSelfCalls')
             ->outIs('METHOD')
             ->atomIs('Method')
             ->is('visibility', 'protected')
             ->analyzerIsNot('Classes/UsedPrivateMethod');
        $this->prepareQuery();
    }
}

?>
