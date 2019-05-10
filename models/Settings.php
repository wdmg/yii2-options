<?php

namespace wdmg\settings\models;

use Yii;

/**
 * This is the model class for table "{{%settings}}".
 *
 * @property int $id
 * @property string $param
 * @property string $value
 * @property string $default
 * @property string $label
 * @property string $type
 */
class Settings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['param', 'value', 'default', 'label', 'type'], 'required'],
            [['value', 'default'], 'string'],
            [['param', 'type'], 'string', 'max' => 128],
            [['label'], 'string', 'max' => 255],
            [['param'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/settings', 'ID'),
            'param' => Yii::t('app/modules/settings', 'Param'),
            'value' => Yii::t('app/modules/settings', 'Value'),
            'default' => Yii::t('app/modules/settings', 'Default'),
            'label' => Yii::t('app/modules/settings', 'Label'),
            'type' => Yii::t('app/modules/settings', 'Type'),
        ];
    }
}
