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

namespace Exakat\Reports;

use Exakat\Reports\Helpers\PhpCodeTree;

class Exakatvendors extends Reports {
    const FILE_EXTENSION = 'ini';
    const FILE_FILENAME  = 'vendors.exakat';

    public function _generate(array $analyzerList): string {
        $stubCode = array();

        $code = new PhpCodeTree($this->dump);
        $code->load();

        $code->map('functions', function ($function) {
            return "function[] = \"$function[function]\";";
        });
        $code->reduce('functions', function ($carry, $item) {
            return $carry . "\n" . $item;
        });

        $code->map('namespaces', function ($namespace) {
            return $namespace['functions'][$namespace['id']]['reduced'] ?? '; No function definitions';
        });

        $code->reduce('namespaces', function ($carry, $item) {
            return $carry . "\n" . $item;
        });

        return $code->get('namespaces');
    }
}

?>