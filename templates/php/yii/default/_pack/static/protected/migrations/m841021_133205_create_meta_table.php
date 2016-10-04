<?php

class m841021_133205_create_meta_table extends CDbMigration
{
	public function up()
	{
		/*
		 * Table structure for MetaData model
		 */
		$this->createTable('meta', array(
			'id' => 'pk',
			'key' => 'VARCHAR(32) NOT NULL',
			'value' => 'TEXT NOT NULL',
			'parent_id' => 'INTEGER UNSIGNED NOT NULL',
			'KEY `parent_id` (`parent_id`)',
		), 'AUTO_INCREMENT=1 CHARSET=utf8 COLLATE=utf8_unicode_ci');
	}
	
	public function down()
	{
		$this->dropTable('meta');
	}
}