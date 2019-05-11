<?php

namespace wdmg\settings\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;

class Settings extends Component
{

    protected $model;
    public $cache = 'cache';
    public $cacheKey = 'wdmg/settings';
    private $settings = null;

    /**
     * Initialize the component
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->model = new \wdmg\settings\models\Settings;
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

        $data = $this->getSettings();
        if (!empty($data[$section][$param][0]) && isset($data[$section][$param][0])) {
            $value = $data[$section][$param][0];
            if (isset($data[$section][$param][1])) {
                $type = $data[$section][$param][1];
                return $this->setType($value, $type);
            } else {
                return $value;
            }
        } else {
            if (!empty($data[$section][$param][2])) {
                $default = $data[$section][$param][2];
                if (isset($data[$section][$param][1])) {
                    $type = $data[$section][$param][1];
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

        if ($this->model->setSetting($section, $param, $value, $type)) {
            $this->clearCache();
            return true;
        }
        return false;
    }

    private function getSettings()
    {
        if ($this->settings === null) {
            if ($this->cache instanceof Cache) {
                $data = $this->cache->get($this->cacheKey);
                if ($data === false) {
                    $data = $this->model->getAllSettings();
                    $this->cache->set($this->cacheKey, $data);
                }
            } else {
                $data = $this->model->getAllSettings();
            }
            $this->settings = $data;
        }
        return $this->settings;
    }

    private function setType($var, $type = null)
    {
        settype($var, $type);
        return $var;
    }

    public function clearCache()
    {
        $this->settings = null;
        if ($this->cache instanceof Cache) {
            return $this->cache->delete($this->cacheKey);
        }
        return true;
    }
}

?>