<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/../app/Views');
$iterator = new RecursiveIteratorIterator($dir);
$errorCount = 0;
$totalCount = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $totalCount++;
        $output = [];
        $returnVar = 0;
        exec('php -l "' . $file->getPathname() . '"', $output, $returnVar);
        if ($returnVar !== 0) {
            echo "ERROR in " . $file->getPathname() . ":\n" . implode("\n", $output) . "\n";
            $errorCount++;
        }
    }
}

echo "CHECKED $totalCount VIEWS. Total errors: $errorCount\n";

