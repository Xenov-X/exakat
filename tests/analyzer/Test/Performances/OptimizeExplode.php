<?php

namespace Test\Performances;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class OptimizeExplode extends Analyzer {
    /* 1 methods */

    public function testPerformances_OptimizeExplode01()  { $this->generic_test('Performances/OptimizeExplode.01'); }
}
?>