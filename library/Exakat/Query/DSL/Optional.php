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


namespace Exakat\Query\DSL;

use Exakat\Query\Query;

class Optional extends DSL {
    public function run() : Command {
        assert(func_num_args() == 2, __CLASS__ . ' requires 2 arguments, ' . func_num_args() . ' provided');
        list($filter, $arguments) = func_get_args();
        
        if ($filter instanceof Command) {
            if ($filter->gremlin === Query::STOP_QUERY) {
                $filter->gremlin = Query::NO_QUERY;
            } else {
                $filter->gremlin = "optional(\n{$filter->gremlin}\n)";
            }
            return $filter;
        } else {
            assert(false, 'Optional requires a command object');
        }
    }
}
?>