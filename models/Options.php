<?php

namespace wdmg\options\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%options}}".
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
class Options extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%options}}';
    }

    /**
     * {@inheritdoc}
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
            [['autoload'], 'boolean'],
            [['autoload'], 'default', 'value' => false],
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
            'id' => Yii::t('app/modules/options', 'ID'),
            'section' => Yii::t('app/modules/options', 'Section'),
            'param' => Yii::t('app/modules/options', 'Param'),
            'value' => Yii::t('app/modules/options', 'Value'),
            'default' => Yii::t('app/modules/options', 'Default'),
            'label' => Yii::t('app/modules/options', 'Label'),
            'type' => Yii::t('app/modules/options', 'Type'),
            'autoload' => Yii::t('app/modules/options', 'Autoload'),
            'created_at' => Yii::t('app/modules/options', 'Created at'),
            'updated_at' => Yii::t('app/modules/options', 'Updated at'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (preg_match('/\./', $this->param)) {
            $split = explode('.', $this->param, 2);
            if (count($split) > 1) {
                if (is_null($this->section) || $this->section === $split[0]) {
                    $this->section = $split[0];
                    $this->param = $split[1];
                }
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions($asArray = true, $onlyAutoload = false)
    {
        $cond = [];
        if($onlyAutoload)
            $cond = ['autoload' => true];

        if($asArray)
            $options = static::find()->where($cond)->asArray()->all();
        else
            $options = static::find()->all();

        if($asArray) {
            return array_merge_recursive(
                ArrayHelper::map($options, 'param', 'value', 'section'),
                ArrayHelper::map($options, 'param', 'type', 'section'),
                ArrayHelper::map($options, 'param', 'default', 'section')
            );
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function setOption($section, $param, $value, $type = null, $label = null, $autoload = false)
    {
        $model = static::findOne(['section' => $section, 'param' => $param]);

        if ($model === null) {
            $model = new static();
            $model->default = strval($value);
        }


        $model->section = $section;
        $model->param = $param;
        $model->value = strval($value);
        $model->autoload = $autoload;

        if ($type !== null)
            $model->type = $type;
        elseif (!isset($model->type))
            $model->type = $this->getTypeByValue($value);

        if ($label !== null)
            $model->label = $label;
        elseif (!isset($model->label))
            $model->label = ucfirst(strtolower(implode(preg_split('/(?<=\\w)(?=[A-Z])/', $param, -1, PREG_SPLIT_NO_EMPTY), " ")));

        return $model->save();
    }

    /**
     * @param $value
     * @return string
     */
    protected function getTypeByValue($value)
    {

        if (filter_var($value, FILTER_VALIDATE_BOOLEAN))
            return 'boolean';

        if (filter_var($value, FILTER_VALIDATE_INT))
            return 'integer';

        if (filter_var($value, FILTER_VALIDATE_FLOAT))
            return 'float';

        $type = gettype($value);
        if ($type === 'object' && !empty($value)) {

            $error = false;
            try {
                Json::decode($value);
            } catch (InvalidArgumentException $e) {
                $error = true;
            }

            if (!$error)
                $type = 'object';

        }

        return $type;
    }
}
