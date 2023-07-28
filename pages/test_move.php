<?php

namespace Stanford\MigrateEdocsToGCP;

/** @var \Stanford\MigrateEdocsToGCP\MigrateEdocsToGCP $module */


try {

    $module->moveTempEdocs();
} catch (\Exception $e) {
    echo $e->getMessage();
}
