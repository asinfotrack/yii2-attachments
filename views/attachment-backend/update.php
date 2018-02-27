<?php
use yii\helpers\Url;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\attachments\models\Attachment */

$this->title = Yii::t('app', 'Update attachment');
?>

<?= Button::widget([
	'tagName'=>'a',
	'icon'=>'list',
	'label'=>Yii::t('app', 'All attachments'),
	'options'=>[
		'href'=>Url::to(['attachment-backend/index']),
		'class'=>'btn btn-primary',
	],
]) ?>
<?= Button::widget([
	'tagName'=>'a',
	'icon'=>'eye',
	'label'=>Yii::t('app', 'Attachment details'),
	'options'=>[
		'href'=>Url::to(['attachment-backend/view', 'id'=>$model->id]),
		'class'=>'btn btn-primary',
	],
]) ?>

<?= $this->render('partials/_form', ['model'=>$model]) ?>
