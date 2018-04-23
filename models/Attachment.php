<?php
namespace asinfotrack\yii2\attachments\models;

use Yii;
use yii\base\ErrorException;
use yii\base\InvalidCallException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\validators\FileValidator;
use yii\web\UploadedFile;
use asinfotrack\yii2\attachments\Module;
use asinfotrack\yii2\toolbox\helpers\PrimaryKey;

/**
 * This is the model class for table "attachment".
 *
 * @property integer $id
 * @property string $model_type
 * @property mixed[] $foreign_pk
 * @property bool $is_avatar
 * @property string $filename
 * @property string $extension
 * @property string $mime_type
 * @property integer $size
 * @property string $title
 * @property string $desc
 * @property integer $created
 * @property integer $created_by
 * @property integer $updated
 * @property integer $updated_by
 *
 * @property string $displayTitle readonly
 * @property string $absolutePath readonly
 * @property bool $fileExists
 * @property \yii\db\ActiveRecord $subject readonly
 *
 * @property \yii\web\IdentityInterface $createdBy
 * @property \yii\web\IdentityInterface $updatedBy
 */
class Attachment extends \yii\db\ActiveRecord
{

	/**
	 * @var \yii\db\ActiveRecord|\asinfotrack\yii2\attachments\behaviors\AttachmentBehavior holds the subject for this attachment
	 */
	protected $subject;

	/**
	 * @var \yii\web\UploadedFile holds the actual file during upload
	 */
	public $uploadedFile;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'attachment';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
    {
        return [
        	'timestamp'=>[
	    		'class'=>TimestampBehavior::className(),
	    		'createdAtAttribute'=>'created',
	    		'updatedAtAttribute'=>'updated',
        	],
        	'blameable'=>[
	    		'class'=>BlameableBehavior::className(),
	    		'createdByAttribute'=>'created_by',
	    		'updatedByAttribute'=>'updated_by',
        	],
        ];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['model_type','filename','extension','mime_type','title','description'], 'trim'],
			[['model_type','filename','extension','mime_type','title','description'], 'default'],
			[['is_avatar'], 'default', 'value'=>false],

			[['model_type','foreign_pk','filename','mime_type','size'], 'required'],
			[['uploadedFile'], 'file', 'skipOnEmpty'=>false, 'when'=>function($model) {
				return $model->isNewRecord;
			}],

			[['size'], 'integer', 'min'=>0],
			[['is_avatar'], 'boolean'],
			[['model_type','filename','extension','mime_type','title'], 'string', 'max'=>255],
			[['description'], 'string'],

			[['is_avatar'], function ($attribute, $params) {
				if (!$this->{$attribute}) return;
				if (!preg_match('/^image\/(jpg|jpeg|png|gif)$/i', $this->mime_type)) {
					$msg = Yii::t('app', 'Only jpg, png and gif files can be used as attachments');
					$this->addError($attribute, $msg);
				}
			}],

			[['mime_type'], function ($attribute, $params) {
				if (!empty($this->subject->mimeTypes)) {
					$fv = new FileValidator(['mimeTypes'=>$this->subject->mimeTypes]);
					$fv->validateAttribute($this, $attribute);
				}
			}],
			[['size'], function ($attribute, $params) {
				if ($this->subject!=null && $this->subject->maxAttachmentSize > 0) {
					$fv = new FileValidator(['maxSize'=>$this->subject->maxAttachmentSize]);
					$fv->validateAttribute($this, $attribute);
				}
			}],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id'=>Yii::t('app', 'ID'),
			'model_type'=>Yii::t('app', 'Table name'),
			'foreign_pk'=>Yii::t('app', 'Foreign PK'),
			'is_avatar'=>Yii::t('app', 'Avatar'),
			'filename'=>Yii::t('app', 'Filename'),
			'extension'=>Yii::t('app', 'Extension'),
			'mime_type'=>Yii::t('app', 'Mime type'),
			'size'=>Yii::t('app', 'File size'),
			'title'=>Yii::t('app', 'Title'),
			'description'=>Yii::t('app', 'Description'),
			'created'=>Yii::t('app', 'Created'),
			'created_by'=>Yii::t('app', 'Created by'),
			'updated'=>Yii::t('app', 'Updated'),
			'updated_by'=>Yii::t('app', 'Updated by'),

			'uploadedFile'=>Yii::t('app', 'Attachment'),
			'displayTitle'=>Yii::t('app', 'Title'),
			'absolutePath'=>Yii::t('app', 'Local path'),
			'fileExists'=>Yii::t('app', 'Local file exists'),
			'subject'=>Yii::t('app', 'Subject'),
		];
	}

	/**
	 * Returns an instance of the query-type for this model
	 * @return \asinfotrack\yii2\attachments\models\query\AttachmentQuery
	 */
	public static function find()
	{
		return new \asinfotrack\yii2\attachments\models\query\AttachmentQuery(get_called_class());
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		$this->foreign_pk = Json::decode($this->foreign_pk);
		parent::afterFind();
	}

	/**
	 * @inheritdoc
	 */
	public function beforeValidate()
	{
		//call parent implementation
		$resParent = parent::beforeValidate();

		//get values from uploaded file

		if (!($this->uploadedFile instanceof UploadedFile)) {
			$this->uploadedFile = UploadedFile::getInstance($this, 'uploadedFile');
		}
		if ($this->uploadedFile !== null) {
			$this->filename = $this->uploadedFile->name;
			$this->extension = strtolower($this->uploadedFile->extension);
			$this->mime_type = FileHelper::getMimeType($this->uploadedFile->tempName);
			$this->size = $this->uploadedFile->size;
		}

		return $resParent;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		if (!parent::beforeSave($insert)) {
			return false;
		}

		$this->foreign_pk = Json::encode($this->foreign_pk);
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function save($runValidation=true, $attributeNames=null)
	{
		//call actual save implementation
		if (!parent::save($runValidation, $attributeNames)) {
			return false;
		}

		//handle the attachment file
		if ($this->uploadedFile !== null) {
			//create base folder if necessary
			$basePath = FileHelper::normalizePath(Yii::getAlias(Module::getInstance()->attachmentAlias));
			if (!file_exists($basePath) || !is_dir($basePath)) {
				if (!FileHelper::createDirectory($basePath, Module::getInstance()->attachmentFolderPermissions, true)) {
					$msg = Yii::t('app', 'Error while creating the base folder of the attachments: {path}', [
						'path'=>$basePath,
					]);
					throw new ErrorException($msg);
				}
			}

			//save file
			if (!$this->uploadedFile->saveAs($this->getAbsolutePath())) {
				return false;
			}
		}

		//set new avatar if necessary
		if ($this->is_avatar) {
			$oldAvatars = Attachment::find()
				->subject($this->subject)
				->isAvatar(true)
				->andWhere(['!=', 'attachment.id', $this->id])
				->all();

			foreach ($oldAvatars as $oldAvatar) {
				/* @var $oldAvatar \asinfotrack\yii2\attachments\models\Attachment */
				$oldAvatar->is_avatar = false;
				if (!$oldAvatar->save(true, ['is_avatar'])) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete()
	{
		if (!parent::beforeDelete()) {
			return false;
		}

		try {
			if (!$this->getFileExists() || !unlink($this->absolutePath)) {
				$this->addError('absolutePath', 'File does not exist or cannot be deleted.');
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Returns either the title (if set) or the filename
	 *
	 * @return string the display title
	 */
	public function getDisplayTitle()
	{
		return empty($this->title) ? $this->filename : $this->title;
	}

	/**
	 * Returns the local absolute path of the actual attachment file.
	 *
	 * @return string the actual local absolute path
	 * @throws \yii\base\InvalidCallException when the subject model was not saved yet
	 */
	public function getAbsolutePath()
	{
		if ($this->isNewRecord) {
			$msg = Yii::t('app', 'The absolute path can only be specified, after the subject model was saved');
			throw new InvalidCallException($msg);
		}

		$basePath = Yii::getAlias(Module::getInstance()->attachmentAlias);
		$path = $basePath . DIRECTORY_SEPARATOR . $this->id;
		if (!empty($this->extension)) $path .= '.' . $this->extension;
		return FileHelper::normalizePath($path);
	}

	/**
	 * Returns whether or not the local file exists
	 *
	 * @return bool
	 */
	public function getFileExists()
	{
		$path = $this->getAbsolutePath();
		return file_exists($path) && !is_dir($path);
	}

	/**
	 * Getter for the subject model
	 *
	 * @return \yii\db\ActiveRecord the subject of this attachment
	 * @throws \yii\base\ErrorException
	 */
	public function getSubject()
	{
		if (!$this->isNewRecord && $this->subject === null) {
			$this->subject = call_user_func([$this->model_type, 'findOne'], $this->foreign_pk);
			if ($this->subject === null) {
				$msg = Yii::t('app', 'Could not find model for attachment `{attachment}`', [
					'attachment'=>$this->id
				]);
				throw new ErrorException($msg);
			}
		}

		return $this->subject;
	}

	/**
	 * Sets the subject-model for this attachment
	 *
	 * @param \yii\db\ActiveRecord $subject the subject model
	 */
	public function setSubject($subject)
	{
		Module::validateSubject($subject);

		$this->model_type = $subject->className();
		$this->foreign_pk = $subject->getPrimaryKey(true);
		$this->subject = $subject;
	}

	/**
	 * Returns the user who created the instance. This relation only works when
	 * `userRelationCallback` is properly configured within the module config.
	 *
	 * @return \yii\db\ActiveQuery the active query of the relation
	 * @throws \yii\base\InvalidCallException when `userRelationCallback is not properly configured
	 */
	public function getCreatedBy()
	{
		$callback = Module::getInstance()->userRelationCallback;
		if (!is_callable($callback)) {
			$msg = Yii::t('app', 'No or invalid `userRelationCallback` specified in Module config');
			throw new InvalidCallException($msg);
		}

		return call_user_func($callback, $this, 'created_by');
	}

	/**
	 * Returns the user who updated the instance. This relation only works when
	 * `userRelationCallback` is properly configured within the module config.
	 *
	 * @return \yii\db\ActiveQuery the active query of the relation
	 * @throws \yii\base\InvalidCallException when `userRelationCallback is not properly configured
	 */
	public function getUpdatedBy()
	{
		$callback = Module::getInstance()->userRelationCallback;
		if (!is_callable($callback)) {
			$msg = Yii::t('app', 'No or invalid `userRelationCallback` specified in Module config');
			throw new InvalidCallException($msg);
		}

		return call_user_func($callback, $this, 'updated_by');
	}

}
