<?php
namespace asinfotrack\yii2\attachments\widgets;

use asinfotrack\yii2\toolbox\components\Icon;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use rmrevin\yii\fontawesome\FA;
use asinfotrack\yii2\attachments\models\Attachment;
use asinfotrack\yii2\toolbox\widgets\Button;
use asinfotrack\yii2\attachments\Module;

/**
 * The form required for attachments, either as a regular form or wrapped
 * within a modal. There is also a method to generate the button to show
 * the modal
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class AttachmentUpload extends \yii\base\Widget
{

	/**
	 * @var \yii\bootstrap\ActiveForm the form instance
	 */
	protected $form;

	/**
	 * @var \yii\bootstrap\Modal the modal instance
	 */
	protected $modal;

	/**
	 * @var \asinfotrack\yii2\attachments\models\Attachment holds the actual attachment model
	 */
	public $model;

	/**
	 * @var \yii\db\ActiveRecord the subject for the attachments
	 */
	public $subject;

	/**
	 * @var string the form view to use
	 */
	public $formView = '@vendor/asinfotrack/yii2-attachments/views/attachment-backend/partials/_form';

	/**
	 * @var bool whether or not to use a modal (defaults to true)
	 */
	public $useModal = true;

	/**
	 * @var int sets the mode for the modal
	 */
	public $mode = self::MODE_AVATAR_OR_ATTACHMENT;

	/**
	 * @var string the if for the modal (if rendered with modal)
	 */
	public $modalId;

	/**
	 * @var bool  whether or not to show the modal immediately
	 */
	public $showModalImmediately = false;

	/**
	 * @var string the title of the modal
	 */
	public $modalTitle;

	/**
	 * @var string holds the content of the modal footer. Defaults to
	 * a regular submit-button for the form
	 */
	public $modalFooter;

	public const MODE_AVATAR = 10;
	public const MODE_ATTACHMENT = 20;
	public const MODE_AVATAR_OR_ATTACHMENT = 30;


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//validate model and subject
		if ($this->model === null || !($this->model instanceof Attachment)) {
			$msg = Yii::t('app', 'No or invalid attachment-model specified');
			throw new InvalidConfigException($msg);
		}
		if ($this->subject === null || !Module::validateSubject($this->subject)) {
			$msg = Yii::t('app', 'No or invalid subject specified');
			throw new InvalidConfigException($msg);
		}

		//set default settings
		if ($this->useModal) {
			if (empty($this->modalId)) {
				$subjectClass = StringHelper::basename($this->subject->className());
				$widgetClass = StringHelper::basename($this->className());
				$this->modalId = Inflector::camel2id($subjectClass . $widgetClass);
			}
			if (empty($this->modalTitle)) {
				$this->modalTitle = Icon::c('upload') . Html::tag('span', Yii::t('app', 'Upload attachment'));
			}
			if (empty($this->modalFooter)) {
				$btn = Html::submitButton(Yii::t('app', 'Upload'), ['class'=>'btn btn-primary']);
				$this->modalFooter = $btn;
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		if ($this->useModal) $this->renderModalBegin();
		echo $this->renderContent();
		if ($this->useModal) $this->renderModalEnd();
	}

	/**
	 * Generates the trigger-button for the modal
	 *
	 * @param string $label optional label, defaults to modal title
	 * @param array $options options for the button
	 * @return string the html-code of the trigger-button
	 */
	public function generateShowModalButton($label=null, $options=[]) {
		Html::addCssClass($options, 'btn-primary');
		$options['data']['toggle'] = 'modal';
		$options['data']['target'] = '#'.$this->modalId;

		return Button::widget([
			'icon'=>'upload',
			'label'=>$label === null ? Yii::t('app', 'Upload attachment') : $label,
			'encodeLabel'=>false,
			'options'=>$options,
		]);
	}

	/**
	 * Renders the actual form
	 *
	 * @return string the content of the modal
	 */
	protected function renderContent()
	{

		return $this->view->render($this->formView, [
			'model'=>$this->model,
			'mode'=>$this->mode,
		]);
	}

	/**
	 * Begins and configures the modal
	 */
	protected function renderModalBegin()
	{
		$modalOptions = [];
		$modalClientOptions = $this->showModalImmediately ? ['show'=>true] : [];

		$this->modal = Modal::begin([
			'id'=>$this->modalId,
			'options'=>$modalOptions,
			'clientOptions'=>$modalClientOptions,
			'header'=>Html::tag('h4', $this->modalTitle),
			'footer'=>$this->modalFooter,
		]);
	}

	/**
	 * Ends the modal
	 */
	protected function renderModalEnd()
	{
		$this->modal->end();
	}

}
