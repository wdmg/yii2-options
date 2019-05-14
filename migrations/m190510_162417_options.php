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
            'param' => $this->string(128)->notNull()->unique(),
            'value' => $this->text()->notNull(),
            'default' => $this->text()->notNull(),
            'type' => $this->string(64)->notNull(),
            'autoload' => $this->boolean()->notNull(),
            'protected' => $this->boolean()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%options}}');
        $this->dropTable('{{%options}}');
    }

}
