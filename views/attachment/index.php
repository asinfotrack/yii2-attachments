<?php

use asinfotrack\yii2\attachments\models\Attachment;
use asinfotrack\yii2\toolbox\widgets\grid\BooleanColumn;use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedActionColumn;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn;
use asinfotrack\yii2\toolbox\widgets\grid\IdColumn;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \asinfotrack\yii2\attachments\models\search\AttachmentSearch */

$this->title = Yii::t('app', 'Attachments');

$typeFilter = [];
foreach (Attachment::find()->select('model_type')->distinct(true)->column() as $type) {
	$typeFilter[$type] = StringHelper::basename($type);
}
array_multisort($typeFilter);
?>

<?= GridView::widget([
	'dataProvider'=>$dataProvider,
	'filterModel'=>$searchModel,
	'columns'=>[
		[
			'class'=>IdColumn::className(),
			'attribute'=>'id',
		],
		[
			'attribute'=>'subject',
			'columnWidth'=>15,
			'filter'=>$typeFilter,
			'value'=>function ($model, $key, $index, $column) {
				$lines = [
					Html::tag('span', StringHelper::basename($model->model_type)),
					Html::tag('code', Json::encode($model->foreign_pk))
				];
				return implode(Html::tag('br'), $lines);
			},
		],
		[
			'class'=>AdvancedDataColumn::className(),
			'attribute'=>'filename',
			'columnWidth'=>20,
		],
		[
			'attribute'=>'size',
			'columnWidth'=>10,
			'value'=>function ($model, $key, $index, $column) {
				return Yii::$app->formatter->asSize($model->size);
			},
		],
		'title',
		[
			'class'=>BooleanColumn::className(),
			'attribute'=>'is_avatar',
		],
		[
			'class'=>AdvancedActionColumn::className(),
			'label'=>Yii::t('app', 'Download'),
			'template'=>'{download}',
			'buttons'=>[
				'download'=>function ($url, $model, $key) {
					return Html::a(FA::icon('download'), ['attachment/download', 'id'=>$model->id]);
				},
			],
		],
		[
			'class'=>AdvancedActionColumn::className(),
		],
	],
]); ?>