<?php

class m920102_003239_create_filecategory_table extends CDbMigration
{
	public function up()
	{
		/*
		 * Table structure for FileCategory model
		 */
		$this->createTable('filecategory', array(
			'id' => 'pk',
			'hidden' => 'TINYINT NOT NULL DEFAULT 0',
			'title' => 'VARCHAR(100) NOT NULL',
		), 'AUTO_INCREMENT=1 CHARSET=utf8 COLLATE=utf8_unicode_ci');
	}
	
	public function down()
	{
		$this->dropTable('filecategory');
	}
}