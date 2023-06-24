<?php

namespace wdmg\options;

/**
 * Yii2 Options
 *
 * @category        Module
 * @version         1.7.0
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-options
 * @copyright       Copyright (c) 2019 - 2023 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use wdmg\helpers\ArrayHelper;
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
    private $version = "1.7.0";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 1;

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
    public function dashboardNavItems($options = null)
    {
        $items = [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/'. $this->id],
            'icon' => 'fa fa-fw fa-cog',
            'active' => in_array(\Yii::$app->controller->module->id, [$this->id])
        ];

	    $options['count'] = 9;
		if (!is_null($options)) {

			if (isset($options['count'])) {
				$items['label'] .= '<span class="badge badge-default float-right">' . $options['count'] . '</span>';
				unset($options['count']);
			}

			if (is_array($options))
				$items = ArrayHelper::merge($items, $options);

		}

        return $items;
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

        // Autoload options from DB to app params
        if (isset($app->options)) {
            Yii::info('Configured options component', __METHOD__);
            if ($this->autoloadOptions) {
                $app->options->autoload();
            }
        }

        // Set of app name
        if ($app->options->get('app.name'))
            Yii::$app->name = $app->options->get('app.name');
        elseif (isset(Yii::$app->params['app.name']))
            Yii::$app->name = Yii::$app->params['app.name'];

    }
}