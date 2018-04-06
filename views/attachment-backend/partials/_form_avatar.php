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

<?= $this->render('_form_callback_input', [
	'form'=>$form, 'model'=>$model, 'attribute'=>'uploadedFile', 'callback'=>$module->fileInputCallback,
]) ?>
<?= $form->field($model, 'is_avatar')->hiddenInput()->label(false) ?>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
