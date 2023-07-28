<?php

namespace Stanford\MigrateEdocsToGCP;

/** @var \Stanford\MigrateEdocsToGCP\MigrateEdocsToGCP $module */


try {
//    $start = $_GET['start'];
//    $end = $_GET['end'];
//    $update  = $_GET['update'];
//
//    if (!$start or !$end) {
//        throw new \Exception('error ');
//    }
    $module->migrateFiles();
} catch (\Exception $e) {
    echo $e->getMessage();
}
