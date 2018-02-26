<?php
namespace asinfotrack\yii2\attachments\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use asinfotrack\yii2\attachments\models\Attachment;

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
			[['id','size','created','created_by','updated','updated_by'], 'integer'],
			[['is_avatar'], 'boolean'],
			[['model_type','foreign_pk','filename','extension','mime_type','title','desc'], 'safe'],
		];
	}

	public function scenarios()
	{
		return Model::scenarios();
	}

	public function search($params, $query=null)
	{
		if ($query === null) $query = Attachment::find();
		$dataProvider = new ActiveDataProvider([
			'query'=>$query,
			'sort'=>[
				'defaultOrder'=>['attachment.created'=>SORT_DESC],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$query->andFilterWhere([
			'id'=>$this->id,
			'is_avatar'=>$this->is_avatar,
			'size'=>$this->size,
			'created'=>$this->created,
			'created_by'=>$this->created_by,
			'updated'=>$this->updated,
			'updated_by'=>$this->updated_by,
		]);

		$query
			->andFilterWhere(['like', 'model_type', $this->model_type])
			->andFilterWhere(['like', 'foreign_pk', $this->foreign_pk])
			->andFilterWhere(['like', 'filename', $this->filename])
			->andFilterWhere(['like', 'extension', $this->extension])
			->andFilterWhere(['like', 'mime_type', $this->mime_type])
			->andFilterWhere(['like', 'title', $this->title])
			->andFilterWhere(['like', 'desc', $this->desc]);

		return $dataProvider;
	}

}
