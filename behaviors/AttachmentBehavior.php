<?php
namespace asinfotrack\yii2\attachments\behaviors;

use Yii;
use yii\base\InvalidConfigException;
use asinfotrack\yii2\attachments\models\Attachment;

class AttachmentBehavior extends \yii\base\Behavior
{

	/**
	 * @var \yii\db\ActiveRecord the owner of the behavior
	 */
	public $owner;

	/**
	 * @var int max number of attachments for the owner. `0` means no limit.
	 */
	public $maxNumAttachments = 0;

	/**
	 * @var int size in bytes which sets the maximum number of bytes per file. `0` means
	 * no limit.
	 */
	public $maxAttachmentSize = 0;

	/**
	 * @var array|string the allowed mime type(s) for the attachments
	 */
	public $mimeTypes;

	/**
	 * @inheritdoc
	 */
	public function attach($owner)
	{
		if (!($owner instanceof \yii\db\ActiveRecord)) {
			$msg = Yii::t('app', 'The `AttachmentBehavior` can only be attached to classes extending `ActiveRecord`');
			throw new InvalidConfigException($msg);
		}

		parent::attach($owner);
	}

	public function getAttachmentQuery()
	{
		return Attachment::find()->subject($this->owner);
	}

	public function getAttachments()
	{
		return $this->getAttachmentQuery()->all();
	}

	public function getNumAttachments()
	{
		return $this->getAttachmentQuery()->count();
	}

	public function hasAttachments()
	{
		return $this->getNumAttachments() > 0;
	}

}
