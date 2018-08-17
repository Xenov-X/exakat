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


namespace Exakat\Query\DSL;

use Exakat\Query\Query;

class InIs extends DSL {
    protected $args = array('atom');

    public function run() {
        list($link) = func_get_args();
        assert($this->assertLink($link));

        assert(func_num_args() <= 1, "Too many arguments for ".__METHOD__);
        if (empty($link)) {
            return new Command('in( )');
        }
        
        $links = makeArray($link);
        $diff = array_intersect($links, self::$availableLinks);
        if (empty($diff)) {
            $this->query->stopQuery();
        } else {
            return new Command('in('.$this->SorA($link).')');
        }
    }
}
?>
