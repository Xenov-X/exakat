<?php

namespace Test\Classes;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class UnreachableConstant extends Analyzer {
    /* 2 methods */

    public function testClasses_UnreachableConstant01()  { $this->generic_test('Classes/UnreachableConstant.01'); }
    public function testClasses_UnreachableConstant02()  { $this->generic_test('Classes/UnreachableConstant.02'); }
}
?>