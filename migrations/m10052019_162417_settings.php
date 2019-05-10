<?php

use yii\db\Migration;

/**
 * Class m10052019_162417_settings
 */
class m10052019_162417_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%settings}}', [
            'id'=> $this->primaryKey(11),
            'param' => $this->string(128)->notNull()->unique(),
            'value' => $this->text()->notNull(),
            'default' => $this->text()->notNull(),
            'label' => $this->string(255)->notNull(),
            'type' => $this->string(128)->notNull()
        ], $tableOptions);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%settings}}');
        $this->dropTable('{{%settings}}');
    }

}
