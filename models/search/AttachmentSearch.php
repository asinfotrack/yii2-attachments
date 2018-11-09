<?php
namespace asinfotrack\yii2\attachments\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use asinfotrack\yii2\attachments\Module;

/**
 * The search model for the attachments
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class AttachmentSearch extends \asinfotrack\yii2\attachments\models\Attachment
{

	public function rules()
	{
		return [
			[['id','ordering','size','created','created_by','updated','updated_by'], 'integer'],
			[['is_avatar'], 'boolean'],
			[['model_type','foreign_pk','filename','extension','mime_type','title','description'], 'safe'],
		];
	}

	public function scenarios()
	{
		return Model::scenarios();
	}

	public function search($params, $query=null)
	{
		if ($query === null) {
			$query = call_user_func([Module::getInstance()->classMap['attachmentModel'], 'find']);
		}

		$dataProvider = new ActiveDataProvider([
			'query'=>$query,
		]);

		if ($this->load($params) && $this->validate()) {
			$query->andFilterWhere([
				'attachment.id'=>$this->id,
				'attachment.ordering'=>$this->ordering,
				'attachment.is_avatar'=>$this->is_avatar,
				'attachment.size'=>$this->size,
				'attachment.created'=>$this->created,
				'attachment.created_by'=>$this->created_by,
				'attachment.updated'=>$this->updated,
				'attachment.updated_by'=>$this->updated_by,
			]);

			$query
				->andFilterWhere(['like', 'attachment.model_type', $this->model_type])
				->andFilterWhere(['like', 'attachment.foreign_pk', $this->foreign_pk])
				->andFilterWhere(['like', 'attachment.filename', $this->filename])
				->andFilterWhere(['like', 'attachment.extension', $this->extension])
				->andFilterWhere(['like', 'attachment.mime_type', $this->mime_type])
				->andFilterWhere(['like', 'attachment.title', $this->title])
				->andFilterWhere(['like', 'attachment.description', $this->description]);
		}

		return $dataProvider;
	}

}
