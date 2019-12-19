<?php

use yii\db\Migration;

/**
 * Class m190727_023358_create_target_setting_tables
 */
class m190727_023358_create_target_setting_tables extends Migration
{
    public $tableName = '{{%target_setting}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // MySql table options
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'type' => $this->string(20)->notNull(), // group、text、select、password、
            'target_type' => $this->string(60)->notNull()->defaultValue(''),
            'target_id' => $this->integer()->notNull()->defaultValue(0),
            'key' => $this->string(60)->notNull(),
            'value' => $this->text(),
            'description' => $this->string(),
            'status' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
        // Indexes
        $this->createIndex('fk_target_type_target_id_key', $this->tableName, ['target_type', 'target_id', 'key'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
        return true;
    }
}
