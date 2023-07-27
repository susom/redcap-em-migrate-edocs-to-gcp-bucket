<?php

namespace Stanford\MigrateEdocsToGCP;

/** @var \Stanford\MigrateEdocsToGCP\MigrateEdocsToGCP $module */


try {
    $start = $_GET['start'];
    $end = $_GET['end'];

    if (!$start or !$end) {
        throw new \Exception('error ');
    }
    $module->migrateManual($start, $end);
} catch (\Exception $e) {
    echo $e->getMessage();
}
