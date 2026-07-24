<?php

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('Modules/'));
$violations = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $contents = file_get_contents($file->getPathname());
        preg_match_all('/use Modules\\\\([A-Za-z0-9]+)\\\\(.+?);/', $contents, $matches, PREG_SET_ORDER);
        $currentModule = explode('/', $file->getPathname())[1];
        foreach ($matches as $match) {
            $importedModule = $match[1];
            if ($importedModule !== $currentModule && $importedModule !== 'Shared') {
                $violations[] = $file->getPathname().' -> '.$match[0];
            }
        }
    }
}
foreach ($violations as $v) {
    echo $v."\n";
}
