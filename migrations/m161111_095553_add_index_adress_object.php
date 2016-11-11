<?php

use yii\db\Migration;

class m161111_095553_add_index_adress_object extends Migration
{
    protected $indexName = 'address_object_level_region_title_idx';
    protected $tableName = '{{%fias_address_object}}';

    public function up()
    {
        $this->createIndex($this->indexName, $this->tableName, 'address_level, region_code, title');
    }

    public function down()
    {
        $this->dropIndex($this->indexName, $this->tableName);
    }
}
