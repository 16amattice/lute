<?php

$zws = mb_chr(0x200B);

function split_join_nulls($s) {
    $zws = mb_chr(0x200B);
    # $zws = '/';
    return implode($zws, explode(' ', $s));
}

# $s = join_nulls(['hi', ' ', 'there', ' ', 'this', ' ', 'is']);
$s = split_join_nulls('hi there this is a test');
echo $s . "\n";

$words = array_map(fn($s) => split_join_nulls($s), ['there', 'is a']);
var_dump($words);

