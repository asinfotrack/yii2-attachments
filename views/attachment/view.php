<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\widgets\DetailView;
use asinfotrack\yii2\attachments\models\Attachment;
use asinfotrack\yii2\attachments\Module;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\attachments\models\Attachment */

$this->title = Yii::t('app', $model->displayTitle);
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
	'icon'=>'pencil',
	'label'=>Yii::t('app', 'Update attachment'),
	'options'=>[
		'href'=>Url::to(['attachment-backend/update', 'id'=>$model->id]),
		'class'=>'btn btn-primary',
	],
]) ?>

<?= DetailView::widget([
	'model'=>$model,
	'attributes'=>[
		[
			'attribute'=>'id',
		],
		[
			'attribute'=>'subject',
			'value'=>implode(Html::tag('br'), [
				Html::tag('span', StringHelper::basename($model->model_type)),
				Html::tag('code', Json::encode($model->foreign_pk))
			]),
		],
		'filename',
		[
			'attribute'=>'size',
			'value'=>Yii::$app->formatter->asSize($model->size),
		],
		'mime_type',
		'is_avatar:bool',
		'title',
		'desc',
	],
]) ?>
