<?php

$zws = mb_chr(0x200B);


/**
 * Returns array of matches in same format as preg_match or preg_match_all
 * @param bool   $matchAll If true, execute preg_match_all, otherwise preg_match
 * @param string $pattern  The pattern to search for, as a string.
 * @param string $subject  The input string.
 * @param int    $offset   The place from which to start the search (in bytes).
 * @return array
 *
 * Ref https://stackoverflow.com/questions/1725227/preg-match-and-utf-8-in-php
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
    return count($parts);
}

$s = '/aquí/';
$s = '/hola/ /aquí/ /Hay/ /un/ /gato/ /y/ /hay/ /Un/ /perro/.';
// $s = '/aquí/ /Hay/ /un/ /gato/.';
$s = str_replace('/', $zws, $s);
echo str_replace($zws, '/', $s) . "\n";

$words = [ 'aquí', "hay{$zws} {$zws}un" ];
// $words = [ 'aquí' ];
# var_dump($words);


class TextItem {
    public string $term;
    public string $text;
    public int $pos;
    public int $OrderEnd;
    public int $length;
    public ?int $termid;
    public array $hides = array();
    public bool $render = true;

    public function OrderEnd(): int {
        return $this->pos + $this->length - 1;
    }

    public function toString(): string {
        $ren = $this->render ? 'true' : 'false';
        return "{$this->term}; {$this->text}; {$this->pos}; {$this->length}; {$this->termid}; render = {$ren}";
    }
}

function get_all_textitems($s, $words) {
    $termmatches = [];

    foreach ($words as $w) {
        $zws = mb_chr(0x200B);
        $pattern = '/' . $zws . '('. $w . ')' . $zws . '/ui';
        $subject = $s;
        $allmatches = pregMatchCapture(true, $pattern, $subject, 0);

        if (count($allmatches) > 0) {
            # echo "in loop\n";
            # echo "===============\n";
            # var_dump($allmatches);
            # var_dump($allmatches[0]);
            # echo "===============\n";
            foreach ($allmatches[1] as $m) {
                # echo "------------\n";
                # var_dump($m);
                $result = new TextItem();
                $result->term = $w;
                $result->text = $m[0];
                $result->pos = get_count_before($subject, $m[1]);
                $result->length = count(explode($zws, $w));
                $result->termid = 42;
                # echo "------------\n";
                $termmatches[] = $result;
            }
        }
        else {
            echo "no match for pattern $pattern \n";
        }
    }

    // Add originals
    $i = 0;
    foreach (explode($zws, $s) as $original_term) {
        $result = new TextItem();
        $result->term = $original_term;
        $result->text = $original_term;
        $result->pos = $i;
        $result->length = 1;
        $result->termid = null;
        $termmatches[] = $result;
        $i += 1;
    }

    return $termmatches;
}


$termmatches = get_all_textitems($s, $words);
echo "Term matches: ------------\n";
foreach ($termmatches as $t) {
    echo $t->term . ' => ' . $t->toString() . "\n";
}
echo "END Term matches: ------------\n";

function calculate_hides(&$items) {
    // var_dump($items);
    // die();
    $isWord = function($i) { return $i->termid != null; };
    $checkwords = array_filter($items, $isWord);
    echo "checking words ----------\n";
    var_dump($checkwords);
    echo "------\n";

    foreach ($checkwords as &$mw) {
        $isContained = function($i) use ($mw) {
            $contained = ($i->pos >= $mw->pos) && ($i->OrderEnd() <= $mw->OrderEnd());
            $equivalent = ($i->pos == $mw->pos) && ($i->OrderEnd() == $mw->OrderEnd()) && ($i->termid == $mw->termid);
            return $contained && !$equivalent;
        };

        $hides = array_filter($items, $isContained);
        echo "checkword {$mw->text} has hides:\n";
        var_dump($hides);
        echo "end hides\n";
        $mw->hides = $hides;
        foreach ($hides as &$hidden) {
            echo "hiding " . $hidden->text . "\n";
            $hidden->render = false;
        }
    }

    return $items;
}


$items = calculate_hides($termmatches);
echo "AFTER CALC ----------\n";
foreach ($items as $i)
    echo $i->toString() . "\n";
echo "END AFTER CALC ----------\n";


function sort_by_order_and_tokencount($items): array
{
    $cmp = function($a, $b) {
        if ($a->pos != $b->pos) {
            return ($a->pos > $b->pos) ? 1 : -1;
        }
        // Fallback: descending order, by token count.
        return ($a->length > $b->length) ? -1 : 1;
    };

    usort($items, $cmp);
    return $items;
}


$items = array_filter($items, fn($i) => $i->render);
$items = sort_by_order_and_tokencount($items);

echo "RENDER ----------\n";
foreach ($items as $i)
    echo $i->toString() . "\n";
echo "RENDER ----------\n";
