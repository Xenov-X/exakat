<?php

$expected     = array('private static $y1', 
                      'static private y $y2', 
                      'var $y5',
                     );

$expected_not = array('private $y3',
                      'var $y4',
                      'var y $y5',
                      'static $y6',
                     );

?>