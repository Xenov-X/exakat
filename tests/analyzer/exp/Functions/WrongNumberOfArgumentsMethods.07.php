<?php

$expected     = array('$a->c(1)',
                      '$a->c( )',
                      '$a->a( )',
                      '$b->a( )',
                      '$b->c( )',
                      '$b->c(3)',
                      '$c->a( )',
                      '$c->c( )',
                      '$c->c(5)',
                     );

$expected_not = array('$a->a(1)',
                      '$a->a(1, 2)',
                      '$a->c(1, 2)',
                      '$b->a(3)',
                      '$b->a(3, 4)',
                      '$b->c(3, 4)',
                      '$c->a(5)',
                      '$c->a(5, 6)',
                      '$c->c(5, 6)',
                     );

?>