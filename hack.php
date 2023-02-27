<?php

require __DIR__.'/vendor/autoload.php';

use App\Entity\Language;
use App\Entity\Term;
use App\Domain\RenderableCalculator;


$spanish = Language::makeSpanish();

$s = '/hola/ /aquí/ /Hay/ /un/ /gato/ /y/ /hay/ /Un/ /perro/.';
$zws = mb_chr(0x200B);
$s = str_replace('/', $zws, $s);
$words = [ new App\Entity\Term($spanish, 'aquí'), new App\Entity\Term($spanish, "hay{$zws} {$zws}un") ];

$rc = new RenderableCalculator();
$items = $rc->main($s, $words);
echo "RENDER ----------\n";
foreach ($items as $i)
    echo $i->toString() . "\n";
echo "RENDER ----------\n";
