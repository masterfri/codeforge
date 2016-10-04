<?php

class m441005_033824_create_file_table extends CDbMigration
{
	public function up()
	{
		/*
		 * Table structure for File model
		 */
		$this->createTable('file', array(
			'id' => 'pk',
			'category_id' => 'INTEGER UNSIGNED NOT NULL',
			'parent_id' => 'INTEGER UNSIGNED NOT NULL',
			'title' => 'VARCHAR(100) NOT NULL',
			'mime' => 'VARCHAR(50) NOT NULL',
			'extension' => 'VARCHAR(10) NOT NULL',
			'size' => 'INTEGER UNSIGNED NOT NULL',
			'width' => 'INTEGER UNSIGNED NOT NULL',
			'height' => 'INTEGER UNSIGNED NOT NULL',
			'user_id' => 'INTEGER UNSIGNED NOT NULL',
			'create_time' => 'INTEGER UNSIGNED NOT NULL',
			'update_time' => 'INTEGER UNSIGNED NOT NULL',
			'path' => 'VARCHAR(100) NOT NULL',
			'KEY `category_id` (`category_id`)',
			'KEY `parent_id` (`parent_id`)',
			'KEY `user_id` (`user_id`)',
		), 'AUTO_INCREMENT=1 CHARSET=utf8 COLLATE=utf8_unicode_ci');
	}
	
	public function down()
	{
		$this->dropTable('file');
	}
}
