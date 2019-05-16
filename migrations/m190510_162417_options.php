<?php

use yii\db\Migration;

/**
 * Class m190510_162417_options
 */
class m190510_162417_options extends Migration
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

        $this->createTable('{{%options}}', [
            'id' => $this->primaryKey(),
            'section' => $this->string(128)->null(),
            'label' => $this->string(255)->notNull(),
            'param' => $this->string(128)->notNull(),
            'value' => $this->text()->notNull(),
            'default' => $this->text()->notNull(),
            'type' => $this->string(64)->notNull(),
            'autoload' => $this->boolean()->notNull(),
            'protected' => $this->boolean()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);
        $this->createIndex('{{%idx-options-params}}', '{{%options}}', ['label', 'section', 'param']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%options}}');
        $this->dropIndex('{{%idx-options-param}}', '{{%options}}');
        $this->dropTable('{{%options}}');
    }

}
