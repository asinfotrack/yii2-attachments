<?php
namespace asinfotrack\yii2\attachments;

use InvalidArgumentException;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use asinfotrack\yii2\attachments\behaviors\AttachmentBehavior;
use asinfotrack\yii2\toolbox\helpers\ComponentConfig;

/**
 * Main class for the attachment module
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Module extends \yii\base\Module
{

	/**
	 * @var string the alias where the attachments will be saved. If the folder does not
	 * exist upon first upload, it will be created implicitly.
	 */
	public $attachmentAlias = '@runtime/yii2-attachment';

	/**
	 * @var int folder permissions of the base folder as specified in `$attachmentAlias`
	 */
	public $attachmentFolderPermissions = 0755;

	/**
	 * @var callable an optional callback to create the input field for the file upload
	 * of a model. Use this callback to implement an external file upload widget. Remember to reconfigure
	 * the validators of the attachment model as well!
	 *
	 * The callback should have the signature as of the following example and return a string
	 * containing the form code of the input field.
	 *
	 * ```php
	 * function ($form, $model, $attribute, $module, $view) {
	 *     return $form->field($model, $attribute)->widget(MyFileUpload::className(), []);
	 * }
	 * ```
	 *
	 * If not set, the file upload of yii2 be rendered.
	 *
	 * @see \asinfotrack\yii2\attachments\Module::defaultFileInput()
	 * @see \asinfotrack\yii2\attachments\models\Attachment::rules()
	 */
	public $fileInputCallback;

	/**
	 * @var callable an optional callback for the user relations as used by the two models
	 * within their blameable behaviors. This callback needs to be set, to use the `createdBy`
	 * and `changedBy` relations of the attachment model.
	 *
	 * The callback needs to have the signature `function ($model, $attribute)`, where `$model`
	 * is the instance of the attachment and `$attribute` is the field to build
	 * the relation upon (created_by or updated_by). The function should return an `ActiveQuery`
	 * the same way a regular relation is specified within yii2.
	 *
	 * Example for a callback:
	 *
	 * ```php
	 * function ($model, $attribute) {
	 *     return $model->hasOne(User::className(), ['id'=>$attribute]);
	 * }
	 * ```
	 */
	public $userRelationCallback;

	/**
	 * @var bool whether or not to enable client validation in backend forms
	 */
	public $backendEnableClientValidation = false;

	/**
	 * @var bool whether or not to enable ajax validation in backend forms
	 */
	public $backendEnableAjaxValidation = false;

	/**
	 * @var array configuration for the access control of the attachment controller.
	 * If set, the config will be added to the behaviors of the controller.
	 */
	public $backendAccessControl = [
		'class'=>'yii\filters\AccessControl',
		'rules'=>[
			[
				'allow'=>true,
				'roles'=>['@'],
			],
		],
	];

	/**
	 * @var array array holding the views which will be used for the attachment backend. The
	 * array is indexed by the action name and the values will be used to get the views. By
	 * default the views of the module will be used.
	 *
	 * To use a local view, use the corresponding view syntax. Usually two slashes are used
	 * to reference your root view path (eg `//my-folder/my-view`).
	 *
	 * See the attachment backend controller for the variables passed to the corresponding views.
	 * @see \asinfotrack\yii2\attachments\controllers\AttachmentBackendController
	 */
	public $backendViews = [
		'index'=>'index',
		'view'=>'view',
		'create'=>'create',
		'update'=>'update',
	];

	/**
	 * @inheritdoc
	 */
	public function __construct($id, $parent=null, $config=[])
	{
		//load the default config for the module
		$localDefaultConfig = require(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
		$config = ArrayHelper::merge($localDefaultConfig, $config);

		parent::__construct($id, $parent, $config);
	}

	/**
	 * This method will be called to create a file input, when no custom
	 * callback is set in the module config
	 *
	 * @param \yii\bootstrap\ActiveForm $form the form instance
	 * @param \asinfotrack\yii2\attachments\models\Attachment $model the attachment model instance
	 * @param string $attribute name of the attribute
	 * @param \asinfotrack\yii2\attachments\Module $module the module instance
	 * @param \yii\web\View $view the active view
	 * @return string the resulting form code for the input
	 */
	public static function defaultFileInput($form, $model, $attribute, $module, $view)
	{
		return $form->field($model, $attribute)->fileinput();
	}
	/**
	 * Validates a subject model
	 *
	 * @param \yii\db\ActiveRecord $subject the subject to check
	 * @param bool $throwException if set to true, an exception will be thrown if not a valid subject
	 * @return bool true if valid
	 */
	public static function validateSubject($subject, $throwException=true)
	{
		if (!($subject instanceof \yii\db\ActiveRecord)) {
			if (!$throwException) return false;
			$msg = Yii::t('app', 'Only classes extending ActiveRecord allowed');
			throw new InvalidArgumentException($msg);
		}
		if ($subject->isNewRecord) {
			if (!$throwException) return false;
			$msg = Yii::t('app', 'Can not add attachments to unsaved subjects');
			throw new InvalidCallException($msg);
		}
		if (!ComponentConfig::hasBehavior($subject, AttachmentBehavior::className())) {
			$msg = Yii::t('app', 'Subjects of attachments need to have the `AttachmentBehavior` attached');
			throw new InvalidConfigException($msg);
		}

		return true;
	}


}
