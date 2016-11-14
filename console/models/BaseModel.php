<?php

/**
 * Base model for ImportModel and UpdateModel
 *
 */

namespace solbianca\fias\console\models;

use Yii;
use solbianca\fias\console\base\Loader;
use yii\base\Model;

use solbianca\fias\models\FiasUpdateLog;

abstract class BaseModel extends Model
{
    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var \solbianca\fias\console\base\SoapResultWrapper
     */
    protected $fileInfo;

    /**
     * @var string|null
     */
    protected $file;

    /**
     * @var \solbianca\fias\console\base\Directory
     */
    protected $directory;

    /**
     * Fias base version
     *
     * @var string
     */
    public $versionId;

    /**
     * @inherit
     *
     * @param Loader $loader
     * @param string|null $file
     * @param array $config
     */
    public function __construct(Loader $loader, $file = null, $config = [])
    {
        parent::__construct($config);

        $this->loader = $loader;
        $this->file = $file;
        if ($file === null){
            //Берем последнюю версию с сайта Fias
            $this->fileInfo = $loader->getLastFileInfo();
        }

        $this->directory = $this->getDirectory($file, $this->loader, $this->fileInfo);
        if ($file === null){
            //Берем последнюю версию с сайта Fias
            $this->versionId = $this->getVersion($this->directory);
        }
    }

    abstract function run();

    /**
     * Save log
     */
    protected function saveLog()
    {
        if (!$log = FiasUpdateLog::findOne(['version_id' => $this->versionId])) {
            $log = new FiasUpdateLog();
            $log->version_id = $this->versionId;
        }

        $log->created_at = time();
        $log->save(false);
    }


    /**
     * Get fias base version
     *
     * @param $directory \solbianca\fias\console\base\Directory
     * @return string
     */
    protected function getVersion($directory)
    {
        return $directory->getVersionId();
    }
}