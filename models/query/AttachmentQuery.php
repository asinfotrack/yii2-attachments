<?php
namespace asinfotrack\yii2\attachments\models\query;

use asinfotrack\yii2\toolbox\helpers\PrimaryKey;
use yii\helpers\Json;

/**
 * Query class for the attachments providing the most common named scopes
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class AttachmentQuery extends \yii\db\ActiveQuery
{

	/**
	 * @inheritdoc
	 */
	public function prepare($builder)
	{
		//add default ordering if none is set explicitly
		if (empty($this->orderBy)) {
			$this->orderSubject();
			$this->orderOrdering();
		}

		return parent::prepare($builder);
	}

	/**
	 * Named scope to return the attachments sorted by their subject (model_type, foreign_pk)
	 *
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery self for chaining
	 */
	public function orderSubject()
	{
		$this->addOrderBy(['attachment.model_type'=>SORT_ASC, 'attachment.foreign_pk'=>SORT_ASC]);

		return $this;
	}

	/**
	 * Named scope to return the attachments sorted by their ordering
	 *
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery self for chaining
	 */
	public function orderOrdering()
	{
		$this->addOrderBy(['attachment.ordering'=>SORT_ASC]);

		return $this;
	}

	/**
	 * Named scope to filter by subject
	 *
	 * @param \yii\db\ActiveRecord $model the subject model
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery self for chaining
	 */
	public function subject($model)
	{
		$this->modelTypes($model);
		$this->andWhere(['attachment.foreign_pk'=>Json::encode($model->getPrimaryKey(true))]);
		return $this;
	}

	/**
	 * Named scope to filter by model classes or active record instances.
	 * Entries can be a mix of strings containing class names or instances directly
	 *
	 * @param string|string[]|\yii\db\ActiveRecord|\yii\db\ActiveRecord[] $models the model class or their class names
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery self for chaining
	 */
	public function modelTypes($models)
	{
		//get actual types
		if (!is_array($models)) $models = [$models];
		$types = [];
		foreach ($models as $model) {
			$type = $model instanceof \yii\db\ActiveRecord ? $model::className() : $model;
			if (!in_array($type, $types)) $types[] = $type;
		}

		$this->andWhere(['attachment.model_type'=>$types]);
		return $this;
	}

	/**
	 * Named scope to filter certain mime types
	 *
	 * @param string[] $mimeTypes the mime types to filter
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery self for chaining
	 */
	public function mimeTypes($mimeTypes)
	{
		if (!is_array($mimeTypes)) $mimeTypes = [$mimeTypes];
		$this->andWhere(['attachment.mime_type'=>$mimeTypes]);
		return $this;
	}

	/**
	 * Named scope to filter images only
	 *
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery self for chaining
	 */
	public function imagesOnly()
	{
		$this->andWhere(['LIKE', 'attachment.mime_type', 'image/%', false]);
		return $this;
	}

	/**
	 * Named scope to filter only result files
	 *
	 * @param boolean $isAvatar whether or not to filter attachments
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery self for chaining
	 */
	public function isAvatar($isAvatar=true)
	{
		$this->andWhere(['attachment.is_avatar'=>$isAvatar ? 1 : 0]);
		return $this;
	}

}
