<?php

namespace wdmg\settings\models;

use Yii;

/**
 * This is the model class for table "{{%settings}}".
 *
 * @property int $id
 * @property string $section
 * @property string $param
 * @property string $value
 * @property string $default
 * @property string $label
 * @property string $type
 * @property string $created_at
 * @property string $updated_at
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
            [['section', 'param', 'value', 'default', 'label', 'type'], 'required'],
            [['value', 'default'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['section', 'param', 'label', 'type'], 'string', 'max' => 255],
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
            'section' => Yii::t('app/modules/settings', 'Section'),
            'param' => Yii::t('app/modules/settings', 'Param'),
            'value' => Yii::t('app/modules/settings', 'Value'),
            'default' => Yii::t('app/modules/settings', 'Default'),
            'label' => Yii::t('app/modules/settings', 'Label'),
            'type' => Yii::t('app/modules/settings', 'Type'),
            'created_at' => Yii::t('app/modules/settings', 'Created At'),
            'updated_at' => Yii::t('app/modules/settings', 'Updated At'),
        ];
    }
}
