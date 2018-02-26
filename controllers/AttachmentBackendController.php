<?php
namespace asinfotrack\yii2\attachments\controllers;

use asinfotrack\yii2\attachments\models\search\AttachmentSearch;
use asinfotrack\yii2\attachments\Module;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * Controller to manage attachments in the backend
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class AttachmentBackendController extends \yii\web\Controller
{

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		//default filters
		$behaviors = [
			'verbs'=>[
				'class'=>VerbFilter::className(),
				'actions'=>[
					'delete'=>['post'],
				],
			],
		];

		//access control filter if provided by module
		$module = Module::getInstance();
		if (!empty($module->backe)) {
			$behaviors['access'] = $module->backendAccessControl;
		}

		return $behaviors;
	}

	public function actionIndex()
	{
		$searchModel = new AttachmentSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render(Module::getInstance()->backendViews['index'], [
			'searchModel'=>$searchModel,
			'dataProvider'=>$dataProvider,
		]);
	}

	public function actionView($id)
	{
		$model = $this->findModel($id);

		return $this->render(Module::getInstance()->backendViews['view'], [
			'model'=>$model,
		]);
	}

	public function actionUpdate($id)
	{
		$model = $this->findModel($id);
		$loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->save()) {
			return $this->redirect(['attachment-backend/view', 'id'=>$model->id]);
		}

		return $this->render(Module::getInstance()->backendViews['update'], [
			'model'=>$model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->findModel($id);
		$model->delete();

		return $this->redirect(['attachment-backend/index']);
	}

	public function actionDownload($id)
	{
		$model = $this->findModel($id);
		$response = Yii::$app->response;

		//assert file exists or return
		if (!$model->fileExists) {
			$msg = Yii::t('app', 'The attachment {file} was not found on the server', ['file'=>$model->filename]);
			Yii::$app->session->setFlash('danger', $msg);
			return $this->goBack();
		}

		//prepare response-vars according to file extension
		$mime = FileHelper::getMimeType($model->absolutePath);
		$inline = true;
		if (in_array($model->extension, ['png','jpg','gif'])) {
			$format = 'img_' . $model->extension;
		} else if ($model->extension === 'pdf') {
			$format = 'pdf';
		} else {
			$format = Response::FORMAT_RAW;
			$inline = false;
		}

		//configure response
		$response->setDownloadHeaders($model->filename, $mime, $inline, $model->size);
		$response->format = $format;
		$response->data = file_get_contents($model->absolutePath);

		return $response;
	}

	/**
	 * Finds the Attachment model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 * @return Attachment the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		$model = Attachment::findOne($id);
		if ($model === null) {
			$msg = Yii::t('app', 'No attachment found with `{value}`', ['value'=>$id]);
			throw new NotFoundHttpException($msg);
		}
		return $model;
	}

}
