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
	 * downloaded. Use the signature: `function ($img)` and return the image object itself.
	 *
	 * The `$img` param is an instance of `\yii\image\ImageDriver`.
	 *
	 * @see \yii\image\ImageDriver
	 */
	public $imagePreparationCallback;

	/**
	 * The run action
	 *
	 * @param array $args the arguments passed to the action
	 * @return mixed the response data
	 */
	public function run($args)
	{
		//check required params
		if (!isset($args['id'])) {
			$msg = Yii::t('app', 'Param `id` is missing');
			throw new InvalidCallException($msg);
		}

		//fetch and validate model
		$query = call_user_func([Module::getInstance()->classMap['attachmentModel'], 'find']);
		$model = $query->where(['attachment.id'=>$args['id']])->isAvatar(true)->one();
		if ($model === null) {
			$msg = Yii::t('app', 'No attachment with the id `{id}` found or it is not marked as an avatar', ['id'=>$args['id']]);
			throw new NotFoundHttpException($msg);
		}

		//perform the response
		$response = Yii::$app->response;
		$response->format = Response::FORMAT_RAW;
		$response->getHeaders()->set('Content-Type', $model->mime_type);

		//prepare the image
		if ($this->imagePreparationCallback !== null) {
			//TODO: implement
			throw new NotSupportedException('Not yet implemented!');
		} else {
			$response->getHeaders()->set('Content-Length', $model->size);
			return file_get_contents($model->absolutePath);
		}
	}

}
