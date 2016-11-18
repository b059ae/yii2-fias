<?php

/**
 * Модель для импорта данных из базы fias в mysql базу
 */

namespace solbianca\fias\console\models;

use solbianca\fias\console\base\Loader;
use Yii;
use solbianca\fias\console\base\XmlReader;
use yii\helpers\Console;
use solbianca\fias\models\FiasAddressObject;
use solbianca\fias\models\FiasAddressObjectLevel;
use yii\console\Exception;
//use solbianca\fias\models\FiasHouse;

class ImportModel extends BaseModel
{
    
    /**
     * Try to use given file else download full file
     *
     * @param $file
     * @param $loader Loader
     * @param $fileInfo \solbianca\fias\console\base\SoapResultWrapper
     * @return \solbianca\fias\console\base\Directory
     * @throws Exception
     */
    protected function getDirectory($file, $loader, $fileInfo)
    {
        if (null !== $file) {
            if (!file_exists($file)) {
                throw new Exception("File {$file} do not exist.");
            }
            $directory = $loader->wrapDirectory(Yii::getAlias($file));
        } else {
            $directory = $loader->loadInitFile($fileInfo);
        }

        return $directory;
    }
    
    /**
     * @throws \Exception
     */
    public function run()
    {
        return $this->import();
    }

    /**
     * Import fias data in base
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function import()
    {
        try {
            Yii::$app->getDb()->createCommand('SET foreign_key_checks = 0;')->execute();
            Yii::$app->getDb()->createCommand('SET autocommit = 0;')->execute();
            Yii::$app->getDb()->createCommand('SET unique_checks = 0;')->execute();

            /*$this->dropIndexes();*/

            $this->importAddressObjectLevel();

            $this->importAddressObject();

            /*$this->importHouse();*/

            /*$this->addIndexes();*/

            $this->saveLog();

            Yii::$app->getDb()->createCommand('SET unique_checks = 1;')->execute();
            Yii::$app->getDb()->createCommand('SET autocommit = 1;')->execute();
            Yii::$app->getDb()->createCommand('SET foreign_key_checks = 1;')->execute();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Import fias address object
     */
    private function importAddressObject()
    {
        Console::output('Импорт адресов обектов');
        FiasAddressObject::import(new XmlReader(
            $this->directory->getAddressObjectFile(),
            FiasAddressObject::XML_OBJECT_KEY,
            array_keys(FiasAddressObject::getXmlAttributes()),
            FiasAddressObject::getXmlFilters()
        ));
    }

    /**
     * Import fias house
     */
    /*private function importHouse()
    {
        Console::output('Импорт домов');
        FiasHouse::import(new XmlReader(
            $this->directory->getHouseFile(),
            FiasHouse::XML_OBJECT_KEY,
            array_keys(FiasHouse::getXmlAttributes()),
            FiasHouse::getXmlFilters()
        ));
    }*/

    /**
     * Import fias address object levels
     */
    private function importAddressObjectLevel()
    {
        Console::output('Импорт типов адресных объектов (условные сокращения и уровни подчинения)');
        FiasAddressObjectLevel::import(
            new XmlReader(
                $this->directory->getAddressObjectLevelFile(),
                FiasAddressObjectLevel::XML_OBJECT_KEY,
                array_keys(FiasAddressObjectLevel::getXmlAttributes()),
                FiasAddressObjectLevel::getXmlFilters()
            )
        );
    }

    /**
     * Get fias base version
     *
     * @param $directory \solbianca\fias\console\base\Directory
     * @return string
     */
    protected function getVersion($directory)
    {
        return $this->fileInfo->getVersionId();
    }

    /**
     * Сбрсываем индексыдля табоиц даееых фиас
     */
    protected function dropIndexes()
    {
        Console::output('Сбрасываем индексы и ключи.');

        Console::output('Сбрасываем внешние ключи.');
//        Yii::$app->getDb()->createCommand()->dropForeignKey('houses_parent_id_fkey', '{{%fias_house}}')->execute();
        Yii::$app->getDb()->createCommand()->dropForeignKey('address_object_parent_id_fkey',
            '{{%fias_address_object}}')->execute();
        Yii::$app->getDb()->createCommand()->dropForeignKey('fk_region_code_ref_fias_region',
            '{{%fias_address_object}}')->execute();

        Console::output('Сбрасываем индексы.');
        Yii::$app->getDb()->createCommand()->dropIndex('region_code_idx', '{{%fias_address_object}}')->execute();
//        Yii::$app->getDb()->createCommand()->dropIndex('house_address_id_fkey_idx', '{{%fias_house}}')->execute();
        Yii::$app->getDb()->createCommand()->dropIndex('address_object_parent_id_fkey_idx',
            '{{%fias_address_object}}')->execute();
        Yii::$app->getDb()->createCommand()->dropIndex('address_object_parent_id_title_fkey_idx',
            '{{%fias_address_object}}')->execute();
        Yii::$app->getDb()->createCommand()->dropIndex('address_object_title_lower_idx',
            '{{%fias_address_object}}')->execute();
        Yii::$app->getDb()->createCommand()->dropIndex('address_object_level_region_title_idx',
            '{{%fias_address_object}}')->execute();

        Console::output('Сбрасываем основные ключи.');
//        Yii::$app->getDb()->createCommand()->dropPrimaryKey('pk', '{{%fias_house}}')->execute();
        Yii::$app->getDb()->createCommand()->dropPrimaryKey('pk', '{{%fias_address_object}}')->execute();
        Yii::$app->getDb()->createCommand()->dropPrimaryKey('pk', '{{%fias_address_object_level}}')->execute();
    }

    /**
     * Устанавливаем индексы для таблиц данных фиас
     */
    protected function addIndexes()
    {
        Console::output('Добавляем к данным индексы и ключи.');

        Console::output('Создаем основные ключи.');
        Yii::$app->getDb()->createCommand()->addPrimaryKey('pk', '{{%fias_address_object}}', 'id')->execute();
        Yii::$app->getDb()->createCommand()->addPrimaryKey('pk', '{{%fias_address_object_level}}',
            ['title', 'code'])->execute();
        /*Yii::$app->getDb()->createCommand()->addPrimaryKey('pk', '{{%fias_house}}', 'id')->execute();*/


        Console::output('Добавляем индексы.');
        Yii::$app->getDb()->createCommand()->createIndex('address_object_parent_id_fkey_idx', '{{%fias_address_object}}', 'parent_id');
        Yii::$app->getDb()->createCommand()->createIndex('address_object_parent_id_title_fkey_idx', '{{%fias_address_object}}', 'parent_id, title');
        Yii::$app->getDb()->createCommand()->createIndex('address_object_title_lower_idx', '{{%fias_address_object}}', 'title');
        Yii::$app->getDb()->createCommand()->createIndex('region_code_idx', '{{%fias_address_object}}', 'region_code');
        Yii::$app->getDb()->createCommand()->createIndex('address_object_level_region_title_idx', '{{%fias_address_object}}', 'address_level, region_code, title');
        /*Yii::$app->getDb()->createCommand()->createIndex('region_code', '{{%fias_address_object}}',
            'region_code')->execute();
        Yii::$app->getDb()->createCommand()->createIndex('house_address_id_fkey_idx', '{{%fias_house}}',
            'address_id')->execute();
        Yii::$app->getDb()->createCommand()->createIndex('address_object_parent_id_fkey_idx',
            '{{%fias_address_object}}',
            'parent_id')->execute();
        Yii::$app->getDb()->createCommand()->createIndex('address_object_title_lower_idx', '{{%fias_address_object}}',
            'title')->execute();*/

        Console::output('Добавляем внешние ключи');
        Yii::$app->getDb()->createCommand()->addForeignKey('address_object_parent_id_fkey', '{{%fias_address_object}}', 'parent_id',
            '{{%fias_address_object}}', 'address_id', 'CASCADE', 'CASCADE');
        Yii::$app->getDb()->createCommand()->addForeignKey('fk_region_code_ref_fias_region', '{{%fias_address_object}}',
            'region_code',
            '{{%fias_region}}', 'code', 'NO ACTION', 'NO ACTION')->execute();
        /*Yii::$app->getDb()->createCommand()->addForeignKey('houses_parent_id_fkey', '{{%fias_house}}', 'address_id',
            '{{%fias_address_object}}',
            'address_id', 'CASCADE', 'CASCADE')->execute();*/



    }
}