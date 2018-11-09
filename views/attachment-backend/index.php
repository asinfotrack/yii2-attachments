<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use asinfotrack\yii2\toolbox\components\Icon;
use asinfotrack\yii2\toolbox\widgets\grid\BooleanColumn;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedActionColumn;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn;
use asinfotrack\yii2\toolbox\widgets\grid\IdColumn;
use asinfotrack\yii2\attachments\Module;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \asinfotrack\yii2\attachments\models\search\AttachmentSearch */

$this->title = Yii::t('app', 'Attachments');

$typeFilter = [];
$query = call_user_func([Module::getInstance()->classMap['attachmentModel'], 'find']);
foreach ($query->select('model_type')->distinct(true)->column() as $type) {
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
			'format'=>'html',
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
			'attribute'=>'ordering',
			'columnWidth'=>5,
			'textAlignAll'=>AdvancedDataColumn::TEXT_CENTER,
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
				return Yii::$app->formatter->asShortSize($model->size);
			},
		],
		'title',
		[
			'class'=>BooleanColumn::className(),
			'attribute'=>'is_avatar',
		],
		[
			'class'=>AdvancedActionColumn::className(),
			'header'=>Yii::t('app', 'Order'),
			'template'=>function ($model, $key, $index) {
				/* @var $model \asinfotrack\yii2\attachments\models\Attachment */
				$buttons = [];
				if (!$model->isOrderedFirst) $buttons[] = '{up}';
				if (!$model->isOrderedLast) $buttons[] = '{down}';
				return implode(' ', $buttons);
			},
			'buttons'=>[
				'up'=>function ($url, $model, $key) {
					return Html::a(Icon::c('arrow-up'), ['attachments/attachment-backend/move-up', 'id'=>$model->id], [
						'title'=>Yii::t('app', 'Move up'),
						'aria-label'=>Yii::t('app', 'Move up'),
						'data-pjax'=>0,
					]);
				},
				'down'=>function ($url, $model, $key) {
					return Html::a(Icon::c('arrow-down'), ['attachments/attachment-backend/move-down', 'id'=>$model->id], [
						'title'=>Yii::t('app', 'Move down'),
						'aria-label'=>Yii::t('app', 'Move down'),
						'data-pjax'=>0,
					]);
				},
			],
		],
		[
			'class'=>AdvancedActionColumn::className(),
			'header'=>Yii::t('app', 'Download'),
			'template'=>'{download}',
			'buttons'=>[
				'download'=>function ($url, $model, $key) {
					return Html::a(Icon::c('download'), ['attachments/attachment-backend/download', 'id'=>$model->id]);
				},
			],
		],
		[
			'class'=>AdvancedActionColumn::className(),
		],
	],
]); ?>
