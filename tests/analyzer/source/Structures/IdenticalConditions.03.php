<?php

// triple
if ($x == 3 || $x == 3 || $w <=> 4) {}

if ($x == 3 || $w <=> 4 || $x == 3) {}
if ($w <=> 4 || $x == 3 || $x == 3) {}

if ($x == 3 || ($w <=> 4 || $x == 3)) {}

if ($w <=> 4 && $x == 3 && $b) {}


?>