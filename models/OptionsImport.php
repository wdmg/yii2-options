<?php

namespace wdmg\options\models;

use yii\base\Model;
use wdmg\options\models\Options;

/**
 * OptionsImport represents the model behind the search form of `wdmg\options\models\Options`.
 */
class OptionsImport extends Options
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['import'], 'file', 'skipOnEmpty' => true, 'minSize' => 1, 'maxSize' => 512000, 'extensions' => 'json'],
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
     * Import or update options
     *
     * @param array $options
     *
     * @return boolean
     */
    public function import($options)
    {
        $count_success = 0;
        $count_fails = 0;
        foreach ($options as $option) {
            $param = $option['param'];
            if(!is_null($option['section']))
                $param = $option['section'].'.'.$option['param'];

            if (Options::setOption($param, $option['value'], $option['type'], $option['label'], $option['autoload'], $option['protected']))
                $count_success++;
            else
                $count_fails++;
        }

        if($count_success > 0 && $count_fails == 0)
            return true;
        else
            return false;
    }

}
