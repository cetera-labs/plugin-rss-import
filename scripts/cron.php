<?php
ini_set('max_execution_time', 0);
ini_set('display_errors', 1);


try {
    $import = new \RssImport\Importer();
    $import->process();
} catch (Exception $e) {
    print_r($e->getMessage());
}
