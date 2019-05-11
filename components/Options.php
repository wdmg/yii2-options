<?php

namespace wdmg\options\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;

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
        if (is_string($this->cache))
            $this->cache = Yii::$app->get($this->cache, false);

    }

    public function get($param, $section = null)
    {
        if (is_null($section)) {
            $split = explode('.', $param, 2);
            if (count($split) > 1) {
                $section = $split[0];
                $param = $split[1];
            }
        }

        $options = $this->getOptions();
        if (!empty($options[$section][$param][0]) && isset($options[$section][$param][0])) {
            $value = $options[$section][$param][0];
            if (isset($options[$section][$param][1])) {
                $type = $options[$section][$param][1];
                return $this->setType($value, $type);
            } else {
                return $value;
            }
        } else {
            if (!empty($options[$section][$param][2])) {
                $default = $options[$section][$param][2];
                if (isset($options[$section][$param][1])) {
                    $type = $options[$section][$param][1];
                    return $this->setType($default, $type);
                } else {
                    return $default;
                }
            } else {
                throw new InvalidArgumentException('Undefined parameter `'.$param.'`');
                return null;
            }
        }
    }

    public function set($param, $value, $section = null, $type = null)
    {
        if (is_null($section) || preg_match('/\./', $param)) {
            $split = explode('.', $param, 2);
            if (count($split) > 1) {
                $section = $split[0];
                $param = $split[1];
            }
        }

        if ($this->model->setOption($section, $param, $value, $type)) {
            $this->clearCache();
            return true;
        }
        return false;
    }

    private function getOptions()
    {
        if ($this->options === null) {
            if ($this->cache instanceof Cache) {
                $options = $this->cache->get($this->cacheKey);
                if ($options === false) {
                    $options = $this->model->getAllOptions();
                    $this->cache->set($this->cacheKey, $options);
                }
            } else {
                $options = $this->model->getAllOptions();
            }
            $this->options = $options;
        }
        return $this->options;
    }

    private function setType($var, $type = null)
    {
        settype($var, $type);
        return $var;
    }

    public function clearCache()
    {
        $this->options = null;
        if ($this->cache instanceof Cache) {
            return $this->cache->delete($this->cacheKey);
        }
        return true;
    }
}

?>