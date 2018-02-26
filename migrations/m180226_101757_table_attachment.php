<?php

/**
 * Migration adding the attachment table as needed by the module
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class m180226_101757_table_attachment extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->createAuditedTable('{{%attachment}}', [
			'id'=>$this->primaryKey(),
			'model_type'=>$this->string()->notNull(),
			'foreign_pk'=>$this->string()->notNull(),
			'is_avatar'=>$this->boolean()->notNull(),
			'filename'=>$this->string()->notNull(),
			'extension'=>$this->string(),
			'mime_type'=>$this->string()->notNull(),
			'size'=>$this->integer()->notNull(),
			'title'=>$this->string(),
			'desc'=>$this->text(),
		]);
		$this->createIndex('IN_attachment_model_type', '{{%attachment}}', ['model_type']);
		$this->createIndex('IN_attachment_model_type_foreign_pk', '{{%attachment}}', ['model_type','foreign_pk']);
		$this->createIndex('IN_attachment_filename', '{{%attachment}}', ['filename']);

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%attachment}}');

		return true;
	}

}
