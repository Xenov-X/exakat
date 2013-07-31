<?php

namespace Tokenizer;

class _Global extends TokenAuto {
    static public $operators = array('T_GLOBAL');

    function _check() {
        $values = array('T_EQUAL', 'T_COMMA');

    // class x { protected $x }
        $this->conditions = array( 0 => array('token' => _Global::$operators),
                                   1 => array('atom' => array('Variable', 'String', 'Staticconstant', 'Static' )),
                                   2 => array('filterOut' => $values),
                                   // T_SEMICOLON because of _Class 28 test
                                 );
        
        $this->actions = array('transform' => array( 1 => 'DEFINE'),
                               'add_void'  => array( 0 => 'VALUE'), 
                               'atom'      => 'Global',
                               );
        $this->checkAuto(); 

    // class x { var $x = 2 }
        $this->conditions = array( 0 => array('token' => _Global::$operators),
                                   1 => array('atom' => 'Assignation'),
                                   2 => array('token' => array('T_SEMICOLON')),
                                 );
        
        $this->actions = array('to_ppp' => true,
                               'atom'   => 'Global',
                               );
        $this->checkAuto(); 

    // class x { var $x, $y }
        $this->conditions = array( 0 => array('token' => _Global::$operators),
                                   1 => array('atom' => 'Arguments'),
                                   2 => array('filterOut' => array('T_COMMA')),
                                 );
        
        $this->actions = array('to_var'   => 'Global',
                               'atom'     => 'Global',
                               );
        $this->checkAuto(); 

        return $this->checkRemaining();
    }
}
?>