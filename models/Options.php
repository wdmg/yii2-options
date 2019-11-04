<?php

namespace wdmg\options\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\Model;
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

    public $model;
    public $import;
    public $typeRange = ['boolean', 'integer', 'float', 'string', 'array', 'object', 'email', 'ip', 'url', 'domain', 'mac', 'regexp'];


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
            ]
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
            //[['autoload', 'protected'], 'default', 'value' => 0],
            [['value', 'default', 'type'], 'checkTypeOfValue'],
            ['type', 'in', 'range' => $this->typeRange],
            ['param', 'checkUniqueParamName'],
            ['param', 'unique', 'targetAttribute' => ['param'], 'message' => Yii::t('app/modules/options', 'Param attribute must be unique.')],
            ['param', 'match', 'pattern' => '/^[A-Za-z0-9.]+$/', 'message' => Yii::t('app/modules/options','It allowed only Latin alphabet, numbers and the character «.»')],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function checkTypeOfValue()
    {
        if (!empty($this->value)) {
            $type = self::getTypeByValue($this->value);
            if (!in_array($type, $this->typeRange)) {
                $this->addError('value', Yii::t('app/modules/options', 'This type `{type}` not supported.', ['type' => $type]));
            }
            if ($this->id && !empty($this->type)) {
                $type = self::getTypeByValue($this->value);
                if ($type !== $this->type && ($this->type !== 'array' && $this->type !== 'object')) {
                    $this->addError('type', Yii::t('app/modules/options', 'The parameter type does not match the value.'));
                }
            }
        }
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
                    $this->addError('param', Yii::t('app/modules/options', 'Param attribute must be unique.'));
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
            'import' => Yii::t('app/modules/options', 'Import file'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $props = $this->getPropsByParam($this->param);
        if(!is_null($props['section'])) {
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
        if ($onlyAutoload)
            $cond = ['autoload' => true];

        if ($asArray)
            $options = static::find()->where($cond)->asArray()->all();
        else
            $options = static::find()->all();

        if ($asArray) {
            $options = array_merge_recursive(
                ArrayHelper::map($options, 'param', 'value', 'section'),
                ArrayHelper::map($options, 'param', 'type', 'section'),
                ArrayHelper::map($options, 'param', 'default', 'section')
            );
        }

        return $options;
    }

    public static function setOption($param, $value, $type = null, $label = null, $autoload = false, $protected = false)
    {
        $props = self::getPropsByParam($param);
        if (!is_null($props['section']))
            $model = static::findOne(['section' => $props['section'], 'param' => $props['param']]);
        else
            $model = static::findOne(['param' => $props['param']]);

        if ($model === null)
            $model = new static();

        if ($type !== null)
            $model->type = $type;
        elseif (!isset($model->type))
            $model->type = self::getTypeByValue($value);

        if ($model->type == "array" || $model->type == "object")
            $model->default = serialize($value);
        else
            $model->default = trim($value);

        $model->param = $param;

        if ($model->type == "array" || $model->type == "object")
            $model->value = serialize($value);
        else
            $model->value = trim($value);

        $model->autoload = $autoload;
        $model->protected = $protected;

        if ($label !== null)
            $model->label = $label;
        elseif (!isset($model->label))
            $model->label = ucfirst(strtolower(implode(preg_split('/(?<=\\w)(?=[A-Z])/', str_replace('.', ' ', $model->param), -1, PREG_SPLIT_NO_EMPTY), " ")));

        if ($model->save())
            return true;
        else
            return $model->errors;

    }

    public function getOptionsTypeList($addAllLabel = true) {

        $items = [];
        if ($addAllLabel)
            $items = ['*' => Yii::t('app/modules/options', 'All types')];
        else
            $items = ['null' => Yii::t('app/modules/options', 'Not selected')];

        return ArrayHelper::merge($items, [
            'boolean' => Yii::t('app/modules/options', 'Boolean'),
            'integer' => Yii::t('app/modules/options', 'Integer'),
            'float' => Yii::t('app/modules/options', 'Integer with float'),
            'string' => Yii::t('app/modules/options', 'String'),
            'ip' => Yii::t('app/modules/options', 'IP'),
            'url' => Yii::t('app/modules/options', 'URL'),
            'email' => Yii::t('app/modules/options', 'Email'),
            'domain' => Yii::t('app/modules/options', 'Domain'),
            'mac' => Yii::t('app/modules/options', 'MAC'),
            'regexp' => Yii::t('app/modules/options', 'RegExp'),
            'array' => Yii::t('app/modules/options', 'Array'),
            'object' => Yii::t('app/modules/options', 'Object')
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

    public static function getTypeByValue($value)
    {
        $type = gettype($value);
        if (is_string($value) || $type == "string")
            $value = trim($value);

        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) || $value === "true" || $value === "false")
            return 'boolean';

        if (filter_var($value, FILTER_VALIDATE_INT))
            return 'integer';

        if (filter_var($value, FILTER_VALIDATE_FLOAT))
            return 'float';

        if (filter_var($value, FILTER_VALIDATE_IP))
            return 'ip';

        if (filter_var($value, FILTER_VALIDATE_EMAIL))
            return 'email';

        if (filter_var($value, FILTER_VALIDATE_MAC))
            return 'mac';

        if (is_string($value) || $type == "string") {
            if(preg_match("/^[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*(\\.[A-Za-z]{2,})$/", $value))
                return 'domain';
            elseif (filter_var($value, FILTER_VALIDATE_URL) || preg_match("/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/", $value))
                return 'url';

            if(preg_match("/^\/[\s\S]+\/[a-zA-Z]{0,6}+$/", $value))
                return 'regexp';
        } else {
            if (($type === 'object' || $type === 'array') && !empty($value)) {

                $error = false;
                try {
                    Json::decode($value);
                } catch (InvalidArgumentException $e) {
                    $error = true;
                }

                if (!$error)
                    $type = 'object';

            }
        }

        if($type)
            return $type;
        else
            return "string";
    }

    public static function getPropsByParam($param) {
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


    /**
     * @return bool whether this widget is associated with a data model.
     */
    public function hasModel($model)
    {
        return ($model instanceof Model && $model->attributes !== null);
    }

}
