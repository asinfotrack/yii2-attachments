<?php
use asinfotrack\yii2\attachments\widgets\AttachmentUpload;use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use asinfotrack\yii2\attachments\Module;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\attachments\models\Attachment */
/* @var $mode int */

$module = Module::getInstance();
$form = ActiveForm::begin([
	'enableClientValidation'=>$module->backendEnableClientValidation,
	'enableAjaxValidation'=>$module->backendEnableAjaxValidation,
	'options'=>[
		'enctype'=>'multipart/form-data',
	]
]);

?>

<?= $form->errorSummary($model); ?>

<?php if (in_array($mode, [AttachmentUpload::MODE_ATTACHMENT, AttachmentUpload::MODE_AVATAR_OR_ATTACHMENT])): ?>
	<fieldset>
		<legend><?= Yii::t('app', 'Attachment information') ?></legend>
		<?= $form->field($model, 'title')->textInput(['maxlength'=>true]) ?>
		<?= $form->field($model, 'description')->textarea(['rows'=>5]) ?>
	</fieldset>
<?php endif; ?>

<fieldset>
	<legend><?= Yii::t('app', 'File') ?></legend>
	<?= $this->render('_form_callback_input', [
		'form'=>$form, 'model'=>$model, 'attribute'=>'uploadedFile', 'callback'=>$module->fileInputCallback,
	]) ?>
</fieldset>

<?php if (in_array($mode, [AttachmentUpload::MODE_AVATAR_OR_ATTACHMENT])): ?>
	<fieldset>
		<legend><?= Yii::t('app', 'Settings') ?></legend>
		<?= $form->field($model, 'is_avatar')->checkbox() ?>
	</fieldset>
<?php endif; ?>

<?php if (in_array($mode, [AttachmentUpload::MODE_AVATAR])): ?>
	<?= $form->field($model,'is_avatar')->hiddenInput(['value'=> true])->label(false) ?>
<?php endif;?>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
