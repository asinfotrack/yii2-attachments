<?php
use yii\db\Query;

/**
 * Migration enabling the sorting of attachments
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class m181001_111757_ordering_of_attachments extends \asinfotrack\yii2\toolbox\console\Migration
{

	public function safeUp()
	{
		$this->addColumn('{{%attachment}}', 'ordering', $this->integer()->notNull()->defaultValue(0)->after('foreign_pk'));
		$this->createIndex('IN_attachment_ordering', '{{%attachment}}', ['ordering']);

		$attachments = (new Query())
			->select(['attachment.id', 'attachment.model_type', 'attachment.foreign_pk'])
			->orderBy(['attachment.model_type'=>SORT_ASC, 'attachment.foreign_pk'=>SORT_ASC, 'attachment.created'=>SORT_ASC])
			->all();

		$curOrdering = -1;
		$curModelType = null;
		$curForeignPk = null;
		foreach ($attachments as $attachment) {
			$id = intval($attachment['id']);

			if (strcasecmp($attachment['model_type'], $curModelType) !== 0 || strcasecmp($attachment['foreign_pk'], $curForeignPk) !== 0) {
				$curOrdering = 1;
				$curModelType = $attachment['model_type'];
				$curForeignPk = $attachment['foreign_pk'];
			}

			$numUpdated = $this->db->createCommand()->update('{{%attachment}}', ['attachment.ordering'=>$curOrdering], ['attachment.id'=>$id])->execute();
			if ($numUpdated !== 1) {
				return false;
			} else {
				$curOrdering++;
			}
		}

		return true;
	}

	public function safeDown()
	{
		$this->dropColumn('{{%attachment}}', 'ordering');

		return true;
	}

}
