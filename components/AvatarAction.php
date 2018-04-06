<?php
namespace asinfotrack\yii2\attachments\components;

use asinfotrack\yii2\attachments\Module;
use Yii;
use yii\base\InvalidCallException;
use yii\base\NotSupportedException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AvatarAction extends \yii\base\Action
{

	/**
	 * @var callable a callback to make final adjustments and modifications for the avatar before it gets
	 * downloaded. Use the signature: `function ($path)` and return the image data the action should return.
	 */
	public $imagePreparationCallback;

	/**
	 * The run action
	 *
	 * @param mixed $id the arguments passed to the action
	 * @return mixed the response data
	 */
	public function run($id)
	{
		//fetch and validate model
		$query = call_user_func([Module::getInstance()->classMap['attachmentModel'], 'find']);
		$model = $query->where(['attachment.id'=>$id])->isAvatar(true)->one();
		if ($model === null) {
			$msg = Yii::t('app', 'No attachment with the id `{id}` found or it is not marked as an avatar', ['id'=>$id]);
			throw new NotFoundHttpException($msg);
		}

		//perform the response
		$response = Yii::$app->response;
		$response->format = Response::FORMAT_RAW;
		$response->getHeaders()->set('Content-Type', $model->mime_type);

		//prepare the image
		if (is_callable($this->imagePreparationCallback)) {
			return call_user_func($this->imagePreparationCallback, $model->absolutePath);
		} else {
			$response->getHeaders()->set('Content-Length', $model->size);
			return file_get_contents($model->absolutePath);
		}
	}

}
