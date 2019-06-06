<?php

namespace wdmg\options;

/**
 * Yii2 Options
 *
 * @category        Module
 * @version         1.3.9
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-options
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;

/**
 * Options module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\options\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = "options/index";

    /**
     * @var string, the name of module
     */
    public $name = "Options";

    /**
     * @var string, the description of module
     */
    public $description = "Storage application options in the database";

    /**
     * @var string the module version
     */
    private $version = "1.3.9";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 3;

    /**
     * @var boolean, autoload options to Yii::$app->params
     */
    public $autoloadOptions = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        // Configure options component
        $app->setComponents([
            'options' => [
                'class' => 'wdmg\options\components\Options'
            ]
        ]);

        // Autoload options from db to app params
        if (isset($module)) {
            if ($module->autoloadOptions) {
                $component = new Options;
                $component->autoload();
            }
        }
    }
}