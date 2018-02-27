<?php
use asinfotrack\yii2\attachments\Module;

/* @var $this \yii\web\View */
/* @var $form \yii\bootstrap\ActiveForm */
/* @var $model \asinfotrack\yii2\attachments\models\Attachment */
/* @var $attribute string */
/* @var $callback callable|\Closure|null */

$module = Module::getInstance();

if (is_callable($callback)) {
	echo call_user_func($callback, $form, $model, $attribute, $module, $this);
} else {
	echo $form->field($model, $attribute)->textInput(['maxlength'=>true]);
}
