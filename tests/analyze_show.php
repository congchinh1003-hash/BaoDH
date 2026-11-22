<?php

$lines = file('app/Views/tickets/show.php');
echo "Total lines: " . count($lines) . "\n";

// Check lines around 596
for ($i = 585; $i <= 610; $i++) {
    echo ($i + 1) . ": " . $lines[$i];
}

// Find all PHP tags and variables
$content = file_get_contents('app/Views/tickets/show.php');
preg_match_all('/<\?=\s*([^;?]+)\s*\?>/', $content, $matches, PREG_OFFSET_CAPTURE);

echo "\nTotal short echo tags: " . count($matches[0]) . "\n";
foreach ($matches[1] as $idx => $match) {
    $expr = trim($match[0]);
    // check if expr has syntax error
    $testCode = "<?php \$x = " . $expr . ";";
    $tokens = @token_get_all($testCode);
    // Find line number
    $lineNum = substr_count(substr($content, 0, $match[1]), "\n") + 1;
    if ($lineNum >= 580 && $lineNum <= 620) {
        echo "Line {$lineNum}: <?= {$expr} ?>\n";
    }
}

