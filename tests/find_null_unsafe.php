<?php

$content = file_get_contents('app/Views/tickets/show.php');
$lines = explode("\n", $content);

echo "Searching for potential null-unsafe functions in show.php...\n";

foreach ($lines as $i => $line) {
    $lineNum = $i + 1;
    // Check htmlspecialchars without ?? or check
    if (preg_match('/htmlspecialchars\s*\(\s*\$([a-zA-Z0-9_]+\[[^\]]+\])\s*\)/', $line, $m)) {
        echo "Line {$lineNum}: htmlspecialchars without fallback: {$m[0]}\n";
    }
    // Check strtotime without null check
    if (preg_match('/strtotime\s*\(\s*\$([a-zA-Z0-9_]+\[[^\]]+\])\s*\)/', $line, $m)) {
        echo "Line {$lineNum}: strtotime without fallback: {$m[0]}\n";
    }
    // Check strtoupper(mb_substr or substr without fallback
    if (preg_match('/substr\s*\(\s*\$([a-zA-Z0-9_]+\[[^\]]+\])\s*,/', $line, $m)) {
        echo "Line {$lineNum}: substr without fallback: {$m[0]}\n";
    }
}

