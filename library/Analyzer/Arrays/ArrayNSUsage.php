<?php

namespace Analyzer\Arrays;

use Analyzer;

class ArrayNSUsage extends Analyzer\Analyzer {
    public $phpVersion = "5.4+";

    public function analyze() {
        $this->atomIs("Array")
             ->is('short_syntax', "true");
        $this->prepareQuery();
    }
}

?>