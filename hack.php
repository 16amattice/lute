<?php

$zws = mb_chr(0x200B);

function split_join_nulls($s) {
    $zws = mb_chr(0x200B);
    # $zws = '/';
    return implode($zws, explode(' ', $s));
}


// Ref https://stackoverflow.com/questions/1725227/preg-match-and-utf-8-in-php
    
/**
 * Returns array of matches in same format as preg_match or preg_match_all
 * @param bool   $matchAll If true, execute preg_match_all, otherwise preg_match
 * @param string $pattern  The pattern to search for, as a string.
 * @param string $subject  The input string.
 * @param int    $offset   The place from which to start the search (in bytes).
 * @return array
 */
function pregMatchCapture($matchAll, $pattern, $subject, $offset = 0)
{
    if ($offset != 0) { $offset = strlen(mb_substr($subject, 0, $offset)); }
        
    $matchInfo = array();
    $method    = 'preg_match';
    $flag      = PREG_OFFSET_CAPTURE;
    if ($matchAll) {
        $method .= '_all';
    }

    var_dump([$method, $pattern, $subject, $matchInfo, $flag, $offset]);
    $n = $method($pattern, $subject, $matchInfo, $flag, $offset);

    $result = array();
    if ($n !== 0 && !empty($matchInfo)) {
        if (!$matchAll) {
            $matchInfo = array($matchInfo);
        }
        foreach ($matchInfo as $matches) {
            $positions = array();
            foreach ($matches as $match) {
                $matchedText   = $match[0];
                $matchedLength = $match[1];
                // dump($subject);
                $positions[]   = array(
                    $matchedText,
                    mb_strlen(mb_strcut($subject, 0, $matchedLength))
                );
            }
            $result[] = $positions;
        }
        if (!$matchAll) {
            $result = $result[0];
        }
    }
    return $result;
}


# $s = join_nulls(['hi', ' ', 'there', ' ', 'this', ' ', 'is']);
$s = split_join_nulls(' hola aquí hay un gato ');
echo $s . "\n";

$words = array_map(fn($s) => split_join_nulls($s), ['aquí', 'hay un']);
# var_dump($words);

foreach ($words as $w) {
    $zws = mb_chr(0x200B);
    $pattern = '/' . $zws . $w . $zws . '/ui';
    $subject = $s;
    var_dump(pregMatchCapture(true, $pattern, $subject, 0));
}