<?php

namespace Test\Classes;

use Test\Analyzer;

include_once dirname(__DIR__, 4).'/library/Autoload.php';
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class UsedPrivateProperty extends Analyzer {
    /* 4 methods */

    public function testClasses_UsedPrivateProperty01()  { $this->generic_test('Classes_UsedPrivateProperty.01'); }
    public function testClasses_UsedPrivateProperty02()  { $this->generic_test('Classes/UsedPrivateProperty.02'); }
    public function testClasses_UsedPrivateProperty03()  { $this->generic_test('Classes/UsedPrivateProperty.03'); }
    public function testClasses_UsedPrivateProperty04()  { $this->generic_test('Classes/UsedPrivateProperty.04'); }
}
?>