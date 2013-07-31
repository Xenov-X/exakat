<?php

namespace Tokenizer;

class _Catch extends TokenAuto {
    static public $operators = array('T_CATCH');

    function _check() {
        $this->conditions = array(0 => array('token' => _Catch::$operators,
                                             'atom' => 'none'),
                                  1 => array('token' => 'T_OPEN_PARENTHESIS'),
                                  2 => array('atom' => 'String'), 
                                  3 => array('atom' => 'Variable'),
                                  4 => array('token' => 'T_CLOSE_PARENTHESIS'),
                                  5 => array('atom' => 'Block'),
                                  );
        
        $this->actions = array('transform'    => array( 1 => 'DROP',
                                                        2 => 'CLASS', 
                                                        3 => 'VARIABLE',
                                                        4 => 'DROP',
                                                        5 => 'CODE',
                                                        ),
                               'cleanIndex' => true,
                               'atom'       => 'Catch');
                               
        $this->checkAuto();

        return $this->checkRemaining();
    }
}

?>