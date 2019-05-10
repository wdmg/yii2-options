<?php

namespace wdmg\settings\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;

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
class Settings extends ActiveRecord
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
            [['param', 'value', 'type'], 'required'],
            [['value', 'default'], 'string'],
            [['section', 'param'], 'string', 'max' => 128],
            [['label'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 64],
            ['type', 'in', 'range' => ['boolean', 'integer', 'float', 'string', 'array', 'object', 'null']],
            [['param'], 'unique', 'targetAttribute' => ['section', 'param']],
            [['created_at', 'updated_at'], 'safe'],
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
            'created_at' => Yii::t('app/modules/settings', 'Created at'),
            'updated_at' => Yii::t('app/modules/settings', 'Updated at'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAllSettings()
    {
        $settings = static::find()->asArray()->all();
        return array_merge_recursive(
            ArrayHelper::map($settings, 'param', 'value', 'section'),
            ArrayHelper::map($settings, 'param', 'type', 'section'),
            ArrayHelper::map($settings, 'param', 'default', 'section')
        );
    }
}
