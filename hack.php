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

    # var_dump([$method, $pattern, $subject, $matchInfo, $flag, $offset]);
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


function get_count_before($string, $pos): int {
    $beforesubstr = mb_substr($string, 0, $pos - 1, 'UTF-8');
    $zws = mb_chr(0x200B);
    $parts = explode($zws, $beforesubstr);
    $nonblank = array_filter($parts, fn($s) => mb_strlen($s) > 0);
    // dump('initial string: ' . $string);
    // dump('getting count before, initial pos = ' . $pos);
    // dump($beforesubstr);
    // dump('all parts:');
    // dump($parts);
    // dump($nonblank);
    return count($nonblank);
}

# $s = join_nulls(['hi', ' ', 'there', ' ', 'this', ' ', 'is']);
$s = '/hola/ /aquí/ /Hay/ /un/ /gato/ /y/ /hay/ /Un/ /perro/.';
$s = str_replace('/', $zws, $s);
echo str_replace($zws, '/', $s) . "\n";

$words = [ 'aquí', "hay{$zws} {$zws}un" ];
# var_dump($words);

$termmatches = [];

foreach ($words as $w) {
    $zws = mb_chr(0x200B);
    $pattern = '/' . $zws . $w . $zws . '/ui';
    $subject = $s;
    $allmatches = pregMatchCapture(true, $pattern, $subject, 0);

    if (count($allmatches) > 0) {
        # echo "in loop\n";
        # echo "===============\n";
        # var_dump($allmatches);
        # var_dump($allmatches[0]);
        # echo "===============\n";
        foreach ($allmatches[0] as $m) {
            # echo "------------\n";
            # var_dump($m);
            $result = [
                'term' => $w,
                'text' => $m[0],
                'pos'=> get_count_before($subject, $m[1]),
                'length' => count(explode($zws, $w)),
                'wordid' => 42
            ];
            # echo "------------\n";
            $termmatches[] = $result;
        }
    }
    else {
        echo "no match for pattern $pattern \n";
    }
}

$i = 0;
foreach (explode($zws, $s) as $original_term) {
    $result = [
        'term' => $original_term,
        'pos' => $i,
        'length' => 1,
        'wordid' => null
    ];
    $termmatches[] = $result;
    $i += 1;
}

foreach ($termmatches as $t) {
    echo $t['term'] . ' => ' . implode('; ', $t) . "\n";
}

