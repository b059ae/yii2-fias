<?php

namespace solbianca\fias\searches;

use solbianca\fias\models\FiasAddressObject;
use solbianca\fias\models\FiasHouse;
use yii\base\Model;
use Yii;
use yii\data\ActiveDataProvider;

class SearchAddress extends Model
{

    /**
     * @var int
     */
    public $region;
    /**
     * @var string
     */
    public $city;
    /**
     * @var string
     */
    public $city_id;
    /**
     * @var string
     */
    public $street;
    /**
     * @var string
     */
    public $street_id;
    /**
     * @var string
     */
    public $type;

    /**
     * Поиск по городам
     *
     * @var array
     */
    const LEVEL_CITY = [1, 4];
    /**
     * Поиск улицам и улицам на дополнительных территориях
     *
     * @var array
     */
    const LEVEL_STREET = [7, 91];

    public $limit = 10;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['city', 'city_id', 'street',], 'string'],
            [['region'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Поиск города и улицы
     * @param $params
     * @return array
     */
    public function searchAddress($params)
    {
        $this->load($params, '');

        switch ($this->type) {
            case 'street':
                $dataProvider = $this->searchStreet($this->region, $this->city_id, $this->street);
                $models = $dataProvider->getModels();
                break;
            case 'city':
                $dataProvider = $this->searchCity($this->region, $this->city);
                $models = $dataProvider->getModels();
                break;
            default:
                return ['result' => true, 'data' => null];
        }

        if (empty($models)) {
            return ['result' => true, 'data' => null];
        }
        /** @var FiasAddressObject $model */
        foreach ($models as $model) {
            $data[] = [
                'value' => $model->getAddress(),
                'id' => $model->address_id
            ];
        }
        return ['result' => true, 'data' => $data];
    }

    /**
     * Поиск города
     * @param integer $region_code
     * @param string $city
     * @return ActiveDataProvider
     */
    public function searchCity($region_code, $city)
    {
        $query = FiasAddressObject::find()->where(['IN', 'address_level', static::LEVEL_CITY]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (empty($region_code) || empty($city)) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'region_code' => $region_code,
        ]);
        $query->andFilterWhere([
            'LIKE',
            'title',
            $city
        ]);

        $query->andFilterWhere([
            '!=',
            'prefix',
            'обл'
        ]);

        $query->limit($this->limit);

        return $dataProvider;
    }

    /**
     * Поиск улицы
     * @param integer $region_code
     * @param string $city_id
     * @param string $street
     * @return ActiveDataProvider
     */
    public function searchStreet($region_code, $city_id, $street)
    {
        $query = FiasAddressObject::find()->where(['IN', 'address_level', static::LEVEL_STREET]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (empty($region_code) || empty($city_id) || empty($street)) {
            return $dataProvider;
        }

        $query->andWhere(['parent_id' => $city_id]);

        $query->andFilterWhere([
            'region_code' => $region_code,
        ]);

        $query->andFilterWhere([
            'LIKE',
            'title',
            $street
        ]);

        $query->limit($this->limit);

        return $dataProvider;
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    /*protected function searchHouse($params)
    {
        $query = FiasHouse::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->where(['address_id' => $this->address_id]);

        $query->andFilterWhere([
            'LIKE',
            FiasHouse::tableName() . '.number',
            $this->house
        ]);

        return $dataProvider;
    }*/
}