<?php

namespace Test\Structures;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class UseArrayFunctions extends Analyzer {
    /* 1 methods */

    public function testStructures_UseArrayFunctions01()  { $this->generic_test('Structures/UseArrayFunctions.01'); }
}
?>