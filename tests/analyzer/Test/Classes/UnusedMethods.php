<?php

namespace Test\Classes;

use Test\Analyzer;

include_once dirname(__DIR__, 4).'/library/Autoload.php';
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class UnusedMethods extends Analyzer {
    /* 6 methods */

    public function testClasses_UnusedMethods01()  { $this->generic_test('Classes_UnusedMethods.01'); }
    public function testClasses_UnusedMethods02()  { $this->generic_test('Classes_UnusedMethods.02'); }
    public function testClasses_UnusedMethods03()  { $this->generic_test('Classes_UnusedMethods.03'); }
    public function testClasses_UnusedMethods04()  { $this->generic_test('Classes_UnusedMethods.04'); }
    public function testClasses_UnusedMethods05()  { $this->generic_test('Classes_UnusedMethods.05'); }
    public function testClasses_UnusedMethods06()  { $this->generic_test('Classes/UnusedMethods.06'); }
}
?>