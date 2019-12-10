<?php

namespace wdmg\options\components;


/**
 * Yii2 Options
 *
 * @category        Component
 * @version         1.5.5
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-options
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;

class Options extends Component
{

    protected $model;
    public $cache = 'cache';
    public $cacheKey = 'wdmg/options';
    private $options = null;

    /**
     * Initialize the component
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->model = new \wdmg\options\models\Options;
        if (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache, false);
        }
    }

    public function autoload() {

        if (Yii::$app->db->schema->getTableSchema($this->model->tableName()) === null)
            return;

        $params = [];
        $data = $this->getOptions(true, false, true);
        foreach ($data as $section => $options) {
            foreach ($options as $param => $option) {
                $type = $option[1];
                $value = $option[0];
                $default = $option[2];

                if ($type == "object" || $type == "array") {
                    try {
                        $value = unserialize($value);
                    } catch (ErrorException $e) {
                        $value = unserialize($default);
                        Yii::warning('Unserialize error: '.$e);
                    }
                }

                if (in_array($type, ["boolean", "bool", "integer", "int", "float", "double", "string", "object", "array", "null"]))
                    settype($value, $type);

                if (!empty($section))
                    $params[$section.'.'.$param] = $value;
                else
                    $params[$param] = $value;

            }
        }
        Yii::info('Autoloaded options from DB to app params', __METHOD__);
        Yii::$app->params = ArrayHelper::merge(Yii::$app->params, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($param) {
        $value = $this->get($param, null, true);
        if (isset($value))
            return $value;

        return parent::__get($param);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($param, $value) {
        if ($this->set($param, $value))
            return true;

        return parent::__set($param, $value);
    }


    public function get($param, $section = null, $hasObject = false)
    {
        $options = $this->getOptions();
        if (!empty($param) && is_null($section) && $hasObject) {
            if (isset($options[$param])) {
                foreach ($options[$param] as $key => $props) {
                    if (isset($props[0]) && !empty($key)) {
                        $value = $props[0];
                        if (isset($props[1])) {
                            $type = $props[1];
                            return (object)[$key => $this->setUpType($value, $type)];
                        } else {
                            return (object)[$key => $value];
                        }
                    } else {
                        continue;
                    }
                }
                return (object)[$param => null];
            } else {
                foreach ($options as $section => $option) {
                    if (empty($section) && array_key_exists($param, $option)) {
                        foreach ($option as $key => $props) {
                            if ($key == $param) {
                                if (isset($props[0])) {
                                    $value = $props[0];
                                    if (isset($props[1])) {
                                        $type = $props[1];
                                        return $this->setUpType($value, $type);
                                    } else {
                                        return $value;
                                    }
                                }
                            } else {
                                continue;
                            }
                        }
                    }
                }
                return null;
            }
        } else {
            if (is_null($section) || preg_match('/\./', $param)) {
                $split = explode('.', $param, 2);
                if (count($split) > 1) {
                    $section = $split[0];
                    $param = $split[1];
                }
            }

            //if (!empty($options[$section][$param][0]) && isset($options[$section][$param][0])) {
            if (isset($options[$section][$param][0])) {
                $value = $options[$section][$param][0];
                if (isset($options[$section][$param][1])) {
                    $type = $options[$section][$param][1];
                    return $this->setUpType($value, $type);
                } else {
                    return $value;
                }
            } else {
                //if (!empty($options[$section][$param][2])) {
                if (isset($options[$section][$param][2])) {
                    $default = $options[$section][$param][2];
                    if (isset($options[$section][$param][1])) {
                        $type = $options[$section][$param][1];
                        return $this->setUpType($default, $type);
                    } else {
                        return $default;
                    }
                } else {
                    return null;
                }
            }
        }
    }

    public function set($param, $value, $type = null, $label = null, $autoload = false, $protected = false)
    {
        $this->clearCache();
        return $this->model->setOption($param, $value, $type, $label, $autoload, $protected);
    }

    private function getOptions($asArray = true, $useCache = true, $onlyAutoload = false)
    {
        if ($this->options === null) {
            if ($useCache && $this->cache instanceof Cache) {
                $options = $this->cache->get($this->cacheKey);
                if ($options === false) {
                    $options = $this->model->getAllOptions($asArray, $onlyAutoload);
                    $this->cache->set($this->cacheKey, $options);
                }
            } else {
                $options = $this->model->getAllOptions($asArray, $onlyAutoload);
            }
            $this->options = $options;
        }
        return $this->options;
    }

    private function setUpType($value, $type = null)
    {
        if (in_array($type, ['email', 'ip', 'url', 'domain', 'mac', 'regexp'], true))
            $type = 'string';

        if ($type == 'boolean' || $type == 'bool') {
            if (intval($value) === 1 || $value === "true") {
                return true;
            } else {
                return false;
            }
        }

        if ($type == 'array') {
            try {
                $value = unserialize($value);
            } catch (ErrorException $e) {
                Yii::warning('Unserialize error: '.$e);
                return false;
            }
        } else {
            settype($value, $type);
        }

        return $value;
    }

    public function delete($option = null)
    {
        if (!is_null($option))
            return $this->model->delete($this->model->tableName(), $this->model->getPropsByParam($option));
        else
            return null;
    }

    public function deleteAll($section = null)
    {
        if (!is_null($section))
            return $this->model->deleteAll(['section' => $section]);
        else
            return null;
    }

    public function clearCache()
    {
        $this->options = null;
        if ($this->cache instanceof Cache) {
            Yii::info('Cache of options has been cleared', __METHOD__);
            return $this->cache->delete($this->cacheKey);
        }
        return true;
    }

}

?>