<?php

class m800910_145431_create_options_table extends CDbMigration
{
	public function up()
	{
		/*
		 * Table structure for OptionRecord model
		 */
		$this->createTable('options', array(
			'id' => 'pk',
			'optname' => 'VARCHAR(32) NOT NULL',
		), 'AUTO_INCREMENT=1 CHARSET=utf8 COLLATE=utf8_unicode_ci');
	}
	
	public function down()
	{
		$this->dropTable('options');
	}
}