<?php

$zws = mb_chr(0x200B);

function split_join_nulls($s) {
    $zws = mb_chr(0x200B);
    # $zws = '/';
    return implode($zws, explode(' ', $s));
}

# $s = join_nulls(['hi', ' ', 'there', ' ', 'this', ' ', 'is']);
$s = split_join_nulls('hola aquí hay un gato');
echo $s . "\n";

$words = array_map(fn($s) => split_join_nulls($s), ['aquí', 'hay un']);
var_dump($words);

