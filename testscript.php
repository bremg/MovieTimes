<?php
$re = '/[1-9]\d*\s*(Episode|E|x|\.)?\s*0*\d*/s';
$str = 'Hap.and.Leonard.S01E06.Eskimos.720p.WEB-DL.DD5.1.H.264-Coo7


';

preg_match($re, $str, $matches);

// Print the entire match result
print_r($matches);