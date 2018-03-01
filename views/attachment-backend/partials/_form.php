<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use asinfotrack\yii2\attachments\Module;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\attachments\models\Attachment */

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

<fieldset>
	<legend><?= Yii::t('app', 'Attachment information') ?></legend>
	<?= $form->field($model, 'title')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'description')->textarea(['rows'=>5]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'File') ?></legend>
	<?= $this->render('_form_callback_input', [
		'form'=>$form, 'model'=>$model, 'attribute'=>'uploadedFile', 'callback'=>$module->fileInputCallback,
	]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Settings') ?></legend>
	<?= $form->field($model, 'is_avatar')->checkbox() ?>
</fieldset>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
