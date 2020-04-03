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


namespace Exakat\Analyzer\Type;

use Exakat\Analyzer\Dump\AnalyzerDump;
use Exakat\Analyzer\Analyzer;

class Email extends Analyzer {
    public function dependsOn() : array {
        return array('Complete/PropagateConstants',
                    );
    }

    protected $analyzerName = 'Email';

    protected $storageType = self::QUERY_RESULTS;

    protected $analyzerTable   = 'results';

    public function analyze() {
        // $x = 'a@b.com';
        $this->atomIs(array('String', 'Concatenation', 'Heredoc'))
             ->hasNoIn('CONCAT')
             ->has('noDelimiter')
             ->regexIs('noDelimiter', '[_A-Za-z0-9-]+(\\\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9]+(\\\\.[A-Za-z0-9]+)*(\\\\.[A-Za-z]{2,})')
             ->toResults();
        $this->prepareQuery();
    }
}

?>
