<?php
/**
 * Inspect Figma sections: print text content + structure.
 */
$j = json_decode(file_get_contents(__DIR__ . '/sections.json'), true);

function walk($node, $depth = 0, &$texts = []) {
    $indent = str_repeat('  ', $depth);
    $name = $node['name'] ?? '?';
    $type = $node['type'] ?? '?';
    if ($type === 'TEXT') {
        $chars = trim($node['characters'] ?? '');
        $size = $node['style']['fontSize'] ?? null;
        $weight = $node['style']['fontWeight'] ?? null;
        $fam = $node['style']['fontFamily'] ?? null;
        $texts[] = compact('chars','size','weight','fam');
        echo "{$indent}TEXT[{$size}/{$weight}] \"" . substr($chars, 0, 80) . "\"\n";
    } else {
        echo "{$indent}{$type} :: {$name}\n";
    }
    foreach ($node['children'] ?? [] as $c) walk($c, $depth + 1, $texts);
    return $texts;
}

$order = ['10:986'=>'HEADER','136:353'=>'OUR SUCCESS','10:904'=>'ALL-IN-ONE HERO','10:886'=>'WHAT IS TOTC','10:873'=>'YOU CAN DO','10:465'=>'OUR FEATURES','10:1461'=>'EXPLORE COURSE','10:421'=>'TESTIMONIALS','10:382'=>'LATEST NEWS','10:359'=>'FOOTER'];

foreach ($order as $id => $label) {
    echo "\n================ $label ($id) ================\n";
    $n = $j['nodes'][$id]['document'] ?? null;
    if ($n) walk($n);
}