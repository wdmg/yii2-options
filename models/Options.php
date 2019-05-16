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
 * @property string $autoload
 * @property string $protected
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
            [['section', 'param'], 'string', 'min' => 3, 'max' => 128],
            [['label'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 64],
            [['autoload', 'protected'], 'boolean'],
            [['autoload', 'protected'], 'default', 'value' => false],
            ['type', 'in', 'range' => ['boolean', 'integer', 'float', 'string', 'array', 'object', 'null']],
            ['param', 'checkUniqueParamName'],
            ['param', 'unique', 'targetAttribute' => ['param'], 'message' => Yii::t('app/modules/options', 'Param attribute must be unique.')],
            ['param', 'match', 'pattern' => '/^[A-Za-z0-9.]+$/', 'message' => Yii::t('app/modules/options','It allowed only Latin alphabet, numbers and the character «.»')],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function checkUniqueParamName()
    {
        if (is_null($this->id)) {
            $props = $this->getPropsByParam($this->param);
            if(is_null($this->section) && !is_null($props['param']) && !is_null($props['section'])) {
                if (!is_null($props['section']) && ($model = static::findOne(['section' => $props['section'], 'param' => $props['param']])) !== null)
                    $this->addError('param', Yii::t('app/modules/options', 'Such a parameter `{param}` already exists in the `{section}` group. Select another parameter name.', $props));
            } else {
                if (($model = static::findOne(['section' => null, 'param' => $props['param']])) !== null)
                    $this->addError('param', Yii::t('app/modules/options', 'Param attribute must be unique7.'));
            }
        }
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
            'protected' => Yii::t('app/modules/options', 'Protected'),
            'created_at' => Yii::t('app/modules/options', 'Created at'),
            'updated_at' => Yii::t('app/modules/options', 'Updated at'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $props = $this->getPropsByParam($this->param);
        if(is_null($this->section) && !is_null($props['section'])) {
            $this->section = $props['section'];
            $this->param = $props['param'];
        } else {
            $this->section = null;
        }

        return parent::beforeSave($insert);
    }

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

    public function setOption($param, $value, $type = null, $label = null, $autoload = false, $protected = false)
    {
        $props = $this->getPropsByParam($this->param);
        if (!is_null($props['section']))
            $model = static::findOne(['section' => $props['section'], 'param' => $param]);
        else
            $model = static::findOne(['param' => $param]);

        if ($model === null) {
            $model = new static();
            $model->default = strval($value);
        }

        $model->param = $param;
        $model->value = strval($value);
        $model->autoload = $autoload;
        $model->protected = $protected;

        if ($type !== null)
            $model->type = $type;
        elseif (!isset($model->type))
            $model->type = $this->getTypeByValue($value);

        if ($label !== null)
            $model->label = $label;
        elseif (!isset($model->label))
            $model->label = ucfirst(strtolower(implode(preg_split('/(?<=\\w)(?=[A-Z])/', str_replace('.', ' ', $model->param), -1, PREG_SPLIT_NO_EMPTY), " ")));

        return $model->save();
    }


    public function getOptionsTypeList($addAllLabel = true) {

        $items = [];
        if ($addAllLabel)
            $items = ['*' => Yii::t('app/modules/options', 'All types')];

        return ArrayHelper::merge($items, [
            'boolean' => Yii::t('app/modules/options', 'Boolean'),
            'integer' => Yii::t('app/modules/options', 'Integer'),
            'float' => Yii::t('app/modules/options', 'Integer with float'),
            'string' => Yii::t('app/modules/options', 'String'),
            'array' => Yii::t('app/modules/options', 'Array'),
            'object' => Yii::t('app/modules/options', 'Object'),
            'null' => Yii::t('app/modules/options', 'NULL'),
        ]);
    }

    public function getAutoloadModeList($addAllLabel = true) {

        $items = [];
        if ($addAllLabel)
            $items = ['*' => Yii::t('app/modules/options', 'All modes')];

        return ArrayHelper::merge($items, [
            '1' => Yii::t('app/modules/options', 'Autoloading'),
            '0' => Yii::t('app/modules/options', 'Not loading'),
        ]);
    }

    public function getFullParamName()
    {
        if (!is_null($this->section) && $this->param)
            return $this->section.'.'.$this->param;

        return $this->param;
    }

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

    protected function getPropsByParam($param) {
        $section = null;
        if (preg_match('/\./', $param)) {
            $split = explode('.', $param, 2);
            if (count($split) > 1) {
                if (!empty($split[0]) && !empty($split[1])) {
                    $section = $split[0];
                    $param = $split[1];
                }
            }
        }
        return [
            'section' => $section,
            'param' => $param,
        ];
    }
}
