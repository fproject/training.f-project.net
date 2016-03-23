<?php
include_once('TXDbMigration.php');
class m160323_181752_create_tables extends TXDbMigration
{
	public function up()
	{
		$this->executeFile('data/u997817970_train.sql');
	}

	public function down()
	{
		echo "m160323_181752_create_tables does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}