<?php

function extractI18nValues($directory) {
    $i18nValues = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if ($file->isFile() && $file->getExtension() === 'js') {
            $content = file_get_contents($file->getRealPath());
            preg_match_all("/i18n\?.*?''/", $content, $matches);
            $i18nValues = array_merge($i18nValues, $matches[0]);
        }
    }

    return $i18nValues;
}



$directory = '/path/to/your/directory';
$i18nValues = extractI18nValues($directory);
print_r($i18nValues);