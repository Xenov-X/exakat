<?php

namespace Test\Structures;

use Test\Analyzer;

include_once dirname(__DIR__, 4).'/library/Autoload.php';
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class UnusedLabel extends Analyzer {
    /* 5 methods */

    public function testStructures_UnusedLabel01()  { $this->generic_test('Structures_UnusedLabel.01'); }
    public function testStructures_UnusedLabel02()  { $this->generic_test('Structures_UnusedLabel.02'); }
    public function testStructures_UnusedLabel03()  { $this->generic_test('Structures_UnusedLabel.03'); }
    public function testStructures_UnusedLabel04()  { $this->generic_test('Structures/UnusedLabel.04'); }
    public function testStructures_UnusedLabel05()  { $this->generic_test('Structures/UnusedLabel.05'); }
}
?>