<?php

namespace wdmg\options\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use wdmg\options\models\Options;

/**
 * OptionsSearch represents the model behind the search form of `wdmg\options\models\Options`.
 */
class OptionsSearch extends Options
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['section', 'param', 'value', 'default', 'label', 'type', 'autoload', 'protected', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Options::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'param', $this->param])
            ->andFilterWhere(['like', 'value', $this->value])
            ->andFilterWhere(['like', 'default', $this->default])
            ->andFilterWhere(['like', 'label', $this->label]);

        if($this->autoload !== "*")
            $query->andFilterWhere(['like', 'autoload', $this->autoload]);

        if($this->type !== "*")
            $query->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }

}
