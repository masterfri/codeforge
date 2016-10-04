<?php

class m450408_165505_create_user_table extends CDbMigration
{
	public function up()
	{
		/*
		 * Table structure for User model
		 */
		$this->createTable('user', array(
			'id' => 'pk',
			'email' => 'VARCHAR(100) NOT NULL',
			'username' => 'VARCHAR(100) NOT NULL',
			'password' => 'VARCHAR(32) NOT NULL',
			'salt' => 'VARCHAR(32) NOT NULL',
			'role' => 'VARCHAR(30) DEFAULT NULL',
			'status' => 'INTEGER NOT NULL DEFAULT 1',
			'date_created' => 'INTEGER UNSIGNED NOT NULL',
			'KEY `email` (`email`(4))',
			'KEY `username` (`username`(4))',
		), 'AUTO_INCREMENT=1 CHARSET=utf8 COLLATE=utf8_unicode_ci');
		/*
		 * Create first user, username: admin, password: qwe
		 */
		$this->insert('user', array( 
			'email' => 'admin@example.com',
			'username' => 'admin',
			'password' => '235c3072d3dd58d88ed495cb746b7fe4',
			'salt' => 'lqrDqJ7TCVenHcr',
			'role' => 'admin',
			'status' => 1,
			'date_created' => time(),
		));
	}
	
	public function down()
	{
		$this->dropTable('user');
	}
}
