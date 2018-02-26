<?php
namespace asinfotrack\yii2\attachments\behaviors;

class AvatarBehavior extends \asinfotrack\yii2\attachments\behaviors\AttachmentBehavior
{

	/**
	 * @var string[] holds the allowed mime types for attachments
	 */
	public $avatarMimeTypes = [
		'image/jpg',
		'image/png',
	];

	/**
	 * Creates an active query instance with the proper scope for this behavior
	 *
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery the active query instance
	 */
	public function getAvatarQuery()
	{
		return $this->getAttachmentQuery()->isAvatar(true)->mimeTypes($this->avatarMimeTypes);
	}

	/**
	 * Checks if the subject has an avatar
	 *
	 * @return bool true if the owner has an avatar
	 */
	public function hasAvatar()
	{
		return $this->getAvatarQuery()->exists();
	}

	/**
	 * Fetches the avatar model of the owner
	 *
	 * @return array|\asinfotrack\yii2\attachments\models\Attachment|null|\yii\db\ActiveRecord
	 */
	public function getAvatarAttachmentModel()
	{
		return $this->getAvatarQuery()->one();
	}

	/**
	 * Returns the modification stamp of the file or null if file not found
	 *
	 * @return int|null either the timestamp or null
	 */
	public function getAvatarModificationStamp()
	{
		try {
			$stamp = filemtime($this->getAvatarAttachmentModel()->absolutePath);
			return $stamp !== false ? $stamp : null;
		} catch (\Exception $e) {
			return null;
		}
	}

}
