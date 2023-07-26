<?php

namespace Stanford\MigrateEdocsToGCP;

use ExternalModules\ExternalModules;
use Google\Cloud\Storage\StorageClient;
use ImageMap\ExternalModule\ExternalModule;

require_once "emLoggerTrait.php";

class MigrateEdocsToGCP extends \ExternalModules\AbstractExternalModule
{

    use emLoggerTrait;

    /**
     * @var StorageClient
     */
    private $GCPClient;

    /**
     * @var \Google\Cloud\Storage\Bucket
     */
    private $bucket;

    public function __construct()
    {
        parent::__construct();

        if ($this->getSystemSetting('gcp-service-account') && $this->getSystemSetting('gcp-project-id')) {
            // init storage client
            $this->setGCPClient(new StorageClient(['keyFile' => json_decode($this->getSystemSetting('gcp-service-account'), true), 'projectId' => $this->getSystemSetting('gcp-project-id')]));

            // init bucket
            $this->setBucket($this->getGCPClient()->bucket($this->getSystemSetting('gcp-bucket-name')));
            // Other code to run when object is instantiated

        }
    }

    /**
     * @return void
     */
    public function migrateFiles()
    {
        $start = $this->getSystemSetting('start-index') ?: 0;
        $end = $this->getSystemSetting('end-index') ?: $this->getSystemSetting('batch-size');
        $sql = sprintf("SELECT * FROM %s WHERE doc_id BETWEEN %s AND %s", db_escape('redcap_edocs_metadata'), db_escape($start), db_escape($end));
        $rows = db_query($sql);
        $pointer = $start;
        while ($row = db_fetch_assoc($rows)) {
            try {
                $pointer++;
                $file_content = file_get_contents(EDOC_PATH . $row['stored_name']);
                if (!$file_content and !file_exists(EDOC_PATH . $row['stored_name'])) {
                    $this->emLog($row['stored_name'] . ' does not exist');
                } elseif (file_exists(EDOC_PATH . $row['stored_name'])) {
                    $this->emLog($row['stored_name'] . ' exists but empty');
                }
                if ($GLOBALS['google_cloud_storage_api_use_project_subfolder']) {
                    $stored_name = $row['project_id'] . '/' . $row['stored_name'];
                }
                $result = $this->getBucket()->upload($file_content, array('name' => $stored_name));
                if ($result) {
                    $this->emLog($stored_name . ' migrated to GCP');
                }
            } catch (\Exception $e) {
                if ($row) {
                    echo '<pre>';
                    print_r($row);
                    echo '</pre>';
                }
                echo $e->getMessage();
                $this->emError($e->getMessage());
            }
        }

        ExternalModules::setSystemSetting($this->PREFIX, 'start-index', (string)($pointer + 1));
        ExternalModules::setSystemSetting($this->PREFIX, 'end-index', (string)($pointer + $this->getSystemSetting('batch-size')));
        echo 'Migration completed for current batch';

    }

    public function MigrateCron()
    {
        $sql = sprintf("SELECT * FROM %s WHERE cron_name = 'migrate_edocs_to_gcp'", db_escape('redcap_crons'));
        $rows = db_query($sql);
        $row = db_fetch_assoc($rows);
        $this->emLog($row);
        if ($row && $row['cron_instances_current'] == '1') {
            $this->emLog( 'start');
            $this->migrateFiles();
        }
        $this->emLog( 'end');
    }

    /**
     * @return StorageClient
     */
    public function getGCPClient(): StorageClient
    {
        return $this->GCPClient;
    }

    /**
     * @param StorageClient $GCPClient
     */
    public function setGCPClient(StorageClient $GCPClient): void
    {
        $this->GCPClient = $GCPClient;
    }

    /**
     * @return \Google\Cloud\Storage\Bucket
     */
    public function getBucket(): \Google\Cloud\Storage\Bucket
    {
        return $this->bucket;
    }

    /**
     * @param \Google\Cloud\Storage\Bucket $bucket
     */
    public function setBucket(\Google\Cloud\Storage\Bucket $bucket): void
    {
        $this->bucket = $bucket;
    }


}
