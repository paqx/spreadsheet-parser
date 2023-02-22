<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%budget}}`.
 */
class m230222_113738_create_budget_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%budget}}', [
            'id' => $this->primaryKey(),
            'product' => $this->string()->notNull()->unique(),
			'category' => $this->string()->notNull(),
            'january' => $this->float(),
            'february' => $this->float(),
			'march' => $this->float(),
            'april' => $this->float(),
            'may' => $this->float(),
            'june' => $this->float(),
            'july' => $this->float(),
            'august' => $this->float(),
			'september' => $this->float(),
			'october' => $this->float(),
			'november' => $this->float(),
			'december' => $this->float()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%budget}}');
    }
}
