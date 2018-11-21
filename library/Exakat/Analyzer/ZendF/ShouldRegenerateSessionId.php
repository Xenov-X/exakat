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

namespace Exakat\Analyzer\ZendF;

use Exakat\Analyzer\Analyzer;
use Exakat\Data\Dictionary;

class ShouldRegenerateSessionId extends Analyzer {
    public function dependsOn() {
        return array('ZendF/UseSession',
                    );
    }
    
    public function analyze() {
        $sessions = $this->query('g.V().hasLabel("Analysis").has("analyzer", "ZendF/UseSession")
                                       .out("ANALYZED").in("RIGHT").hasLabel("Assignation")
                                       .out("LEFT").values("fullcode")')
                         ->toArray();
        // No session, no regenerateId
        if (empty($sessions)) {
            return ;
        }
        
        $sessionsList = makeList($sessions);
        $sessionsList = str_replace('$', '\\$', $sessionsList);
        $regenerateid = $this->dictCode->translate(array('regenerateid'), Dictionary::CASE_INSENSITIVE);
        
        if (empty($regenerateid)) {
            $this->atomIs('Project');
            $this->prepareQuery();

            return;
        }
        
        $result = $this->query('g.V().hasLabel("Methodcall")
                                 .where( __.out("METHOD").filter{it.get().value("lccode") == "$regenerateid"} )
                                 .where( __.out("OBJECT").has("fullcode", within('.$sessionsList.')) )
                                 .count()');

        if ($result->toString() === "0") {
            $this->atomIs('Project');
            $this->prepareQuery();
        }
    }
}

?>
