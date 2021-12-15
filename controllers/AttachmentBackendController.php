<?php
namespace asinfotrack\yii2\attachments\controllers;

use asinfotrack\yii2\attachments\models\Attachment;
use asinfotrack\yii2\toolbox\helpers\Url;
use Yii;
use yii\base\InvalidCallException;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use asinfotrack\yii2\attachments\Module;
use yii\web\ServerErrorHttpException;

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
		if (!empty($module->backendAccessControl)) {
			$behaviors['access'] = $module->backendAccessControl;
		}

		return $behaviors;
	}

	public function actionIndex()
	{
		$searchModel = Yii::createObject(Module::getInstance()->classMap['attachmentSearchModel']);
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
			return $this->goBack(['attachment-backend/view', 'id'=>$model->id]);
		}

		if (!$loaded) {
			Yii::$app->getUser()->setReturnUrl(Yii::$app->request->referrer);
		}
		return $this->render(Module::getInstance()->backendViews['update'], [
			'model'=>$model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->findModel($id);

		$transaction = Yii::$app->db->beginTransaction();

		// update sort index of attachments
		$attachmentsToUpdate = Attachment::find()->subject($model->subject)->andWhere(['>', 'attachment.ordering', $model->ordering])->all();
		if (!empty($attachmentsToUpdate)) {
			foreach ($attachmentsToUpdate as $attachment) {
				/** @var Attachment $attachment */
				$attachment->ordering--;

				if (!$attachment->save()) {
					$transaction->rollBack();
					throw new ServerErrorHttpException(Yii::t('app', 'Error while deleting attachment'));
				}
			}
		}

		if (false === $model->delete()) {
			$transaction->rollBack();
			throw new ServerErrorHttpException(Yii::t('app', 'Error while deleting attachment'));
		}

		$transaction->commit();
		return $this->redirect(Yii::$app->request->referrer ?? ['attachment-backend/index']);
	}

	public function actionMoveUp($id)
	{
		/* @var $model \asinfotrack\yii2\attachments\models\Attachment */
		/* @var $otherModel \asinfotrack\yii2\attachments\models\Attachment */

		$model = $this->findModel($id);
		if ($model->isOrderedFirst) {
			throw new InvalidCallException(Yii::t('app', 'The attachment is already all the way up'));
		}

		$targetOrdering = intval($model->ordering - 1);
		$otherModel = Attachment::find()->subject($model->subject)->andWhere(['attachment.ordering'=>$targetOrdering])->one();
		if ($otherModel === null) {
			throw new ServerErrorHttpException(Yii::t('app', 'Model to switch position with could not be found'));
		}

		if ($this->flipOrdering($model, $otherModel)) {
			return $this->redirect(Yii::$app->request->referrer ?? ['attachment-backend/index']);
		} else {
			throw new ServerErrorHttpException(Yii::t('app', 'Error while updating ordering on attachments'));
		}
	}

	public function actionMoveDown($id)
	{
		/* @var $model \asinfotrack\yii2\attachments\models\Attachment */
		/* @var $otherModel \asinfotrack\yii2\attachments\models\Attachment */

		$model = $this->findModel($id);
		if ($model->isOrderedLast) {
			throw new InvalidCallException(Yii::t('app', 'The attachment is already all the way down'));
		}

		$targetOrdering = intval($model->ordering + 1);
		$otherModel = Attachment::find()->subject($model->subject)->andWhere(['attachment.ordering'=>$targetOrdering])->one();
		if ($otherModel === null) {
			throw new ServerErrorHttpException(Yii::t('app', 'Model to switch position with could not be found'));
		}

		if ($this->flipOrdering($model, $otherModel)) {
			return $this->redirect(Yii::$app->request->referrer ?? ['attachment-backend/index']);
		} else {
			throw new ServerErrorHttpException(Yii::t('app', 'Error while updating ordering on attachments'));
		}
	}

	public function actionDownload($id, $fileName = '')
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
	 * @return \asinfotrack\yii2\attachments\models\Attachment the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		$model = call_user_func([Module::getInstance()->classMap['attachmentModel'], 'findOne'], $id);
		if ($model === null) {
			$msg = Yii::t('app', 'No attachment found with `{value}`', ['value'=>$id]);
			throw new NotFoundHttpException($msg);
		}
		return $model;
	}

	/**
	 * Flips the ordering of two attachment models in the db and returns the result. The operation
	 * is done within a transaction to prevent partial modifications
	 *
	 * @param \asinfotrack\yii2\attachments\models\Attachment $modelA
	 * @param \asinfotrack\yii2\attachments\models\Attachment $modelB
	 * @return bool true upon success
	 * @throws \yii\base\ErrorException
	 * @throws \yii\db\Exception
	 */
	protected function flipOrdering($modelA, $modelB)
	{
		$orderingA = $modelA->ordering;
		$modelA->ordering = $modelB->ordering;
		$modelB->ordering = $orderingA;

		$transaction = Yii::$app->db->beginTransaction();
		if ($modelA->save(true, ['ordering']) && $modelB->save(true, ['ordering'])) {
			$transaction->commit();
			return true;
		} else {
			$transaction->rollBack();
			return false;
		}
	}

}
