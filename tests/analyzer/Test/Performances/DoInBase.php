<?php

namespace Test\Performances;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class DoInBase extends Analyzer {
    /* 1 methods */

    public function testPerformances_DoInBase01()  { $this->generic_test('Performances/DoInBase.01'); }
}
?>