<?php
namespace asinfotrack\yii2\attachments\behaviors;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
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
	public function events()
	{
		return [
			BaseActiveRecord::EVENT_BEFORE_DELETE=>[$this, 'handleDelete'],
		];
	}

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

	/**
	 * Creates a preconfigured query instance
	 *
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery the query
	 */
	public function getAttachmentQuery()
	{
		return Attachment::find()->subject($this->owner);
	}

	/**
	 * Returns the model instances of the assigned attachments
	 *
	 * @return \asinfotrack\yii2\attachments\models\Attachment[] the attachment models
	 */
	public function getAttachments()
	{
		return $this->getAttachmentQuery()->all();
	}

	/**
	 * Returns the number of attachments
	 *
	 * @return int number of assigned attachments
	 */
	public function getNumAttachments()
	{
		return $this->getAttachmentQuery()->count();
	}

	/**
	 * Returns whether or not there are assigned attachments
	 *
	 * @return bool true if there are any
	 */
	public function hasAttachments()
	{
		return $this->getNumAttachments() > 0;
	}

	/**
	 * Event handler for delete events which also deletes all related attachments
	 *
	 * @param \yii\base\ModelEvent $event the event object
	 */
	public function handleDelete($event)
	{
		foreach ($this->getAttachments() as $attachment) {
			if (!$attachment->delete()) {
				$event->isValid = false;
				break;
			}
		}
	}

}
