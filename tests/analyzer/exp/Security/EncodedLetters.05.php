<?php

$expected     = array('"\u{00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000041}"', 
                      '"\u{000000000041}"', 
                      '"\u{00000000041}"', 
                      '"\u{0000000041}"', 
                      '"\u{000000041}"', 
                      '"\u{00000041}"', 
                      '"\u{00000041}"', 
                      '"\u{000041}"', 
                      '"\u{0000041}"', 
                      '"\u{00041}"', 
                      '"\u{0041}"', 
                      '"\u{041}"',
                     );

$expected_not = array('"\u{00011}"', 
                      '"\u{0011}"', 
                      '"\u{011}"',
                     );

?>