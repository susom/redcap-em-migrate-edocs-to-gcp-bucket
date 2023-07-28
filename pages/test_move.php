<?php

namespace Stanford\MigrateEdocsToGCP;

/** @var \Stanford\MigrateEdocsToGCP\MigrateEdocsToGCP $module */


try {

    $module->testMove();
} catch (\Exception $e) {
    echo $e->getMessage();
}
