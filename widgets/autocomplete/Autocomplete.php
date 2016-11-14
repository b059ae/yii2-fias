<?php


namespace solbianca\fias\widgets\autocomplete;

use solbianca\fias\models\FiasRegion;
use yii\base\Widget;

class Autocomplete extends Widget
{
    /**
     * URL to provide autocomplete
     *
     * @var string
     */
    public $url = '/fias/search/autocomplete';


    /**
     * @inherit
     *
     * @return string
     */
    public function run()
    {
        return $this->render('index',
            [
                'urlAddressObject' => $this->url,
                'regions' => $this->getRegions()
            ]
        );
    }

    public function getRegions()
    {
        $regions = FiasRegion::find()->all();

        if (empty($regions)) {
            return [];
        }

        $result = [];
        foreach ($regions as $region) {
            $result[strval($region->code)] = $region->title;
        }

        return $result;
    }
}