<?php
/*
 * Copyright 2012-2018 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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

class DependantTrait extends Analyzer {
    public function analyze() {
        $MAX_LOOPING = self::MAX_LOOPING;
        
        // Case for $this->method()
        $this->atomIs('Trait')
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Methodcall')
             ->outIs('OBJECT')
             ->atomIs('This')
             ->inIs('OBJECT')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'methode')
             ->back('first')
             ->raw(<<<GREMLIN
not(    
    where( 
        __.emit().repeat( out("USE").hasLabel("Usetrait").out("USE").in("DEFINITION") ).times($MAX_LOOPING)
          .out("METHOD", "MAGICMETHOD")
          .hasLabel("Method", "Magicmethod")
          .filter{ it.get().value("lccode") == methode } 
         ) 
    )
GREMLIN
)
             ->back('first');
        $this->prepareQuery();

        // Case for $this->$properties
        $this->atomIs('Trait')
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Member')
             ->outIs('OBJECT')
             ->atomIs('This')
             ->inIs('OBJECT')
             ->outIsIE('VARIABLE') // for arrays
             ->hasNoIn('DEFINITION')
             ->back('first');
        $this->prepareQuery();

        // Case for class::$properties
        $this->atomIs('Trait')
             ->savePropertyAs('fullnspath', 'fnp')
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Staticproperty')
             ->outIs('CLASS')
             ->has('fullnspath')
             ->samePropertyAs('fullnspath', 'fnp')
             ->inIs('CLASS')
             ->outIs('MEMBER')
             ->tokenIs('T_VARIABLE')
             ->savePropertyAs('code', 'property')
             ->goToTrait()
             ->raw(<<<GREMLIN
not( 
    where( 
        __.emit().repeat( out("USE").hasLabel("Usetrait").out("USE").in("DEFINITION") ).times($MAX_LOOPING)
                 .out("PPP")
                 .hasLabel("Ppp")
                 .out("PPP")
                 .coalesce(__.out("LEFT"), 
                           __.filter{ true }
                          )
                 .filter{ it.get().value("code") == property } 
    ) 
)
GREMLIN
)
             ->back('first');
        $this->prepareQuery();

        // Case for class::methodcall
        $this->atomIs('Trait')
             ->savePropertyAs('fullnspath', 'fnp')
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Staticmethodcall')
             ->outIs('CLASS')
             ->tokenIs(self::$STATICCALL_TOKEN)
             ->samePropertyAs('fullnspath', 'fnp')
             ->inIs('CLASS')
             ->outIs('METHOD')
             ->hasNoIn('DEFINITION')
             ->back('first');
        $this->prepareQuery();

        // Case for class::methodcall

        // Case for class::constant
        // self will be solved at excution time, but is set to the trait statically
        $this->atomIs('Trait')
             ->savePropertyAs('fullnspath', 'fnp')
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->outIs('BLOCK')
             ->atomInsideNoDefinition('Staticconstant')
             ->outIs('CLASS')
             ->tokenIs(self::$STATICCALL_TOKEN)
             ->samePropertyAs('fullnspath', 'fnp')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
