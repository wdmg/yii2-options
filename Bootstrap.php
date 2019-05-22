<?php

namespace wdmg\options;

/**
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 */

use yii\base\BootstrapInterface;
use Yii;
use wdmg\options\components\Options;


class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        // Get the module instance
        $module = Yii::$app->getModule('options');

        // Get URL path prefix if exist
        $prefix = (isset($module->routePrefix) ? $module->routePrefix . '/' : '');

        // Add module URL rules
        $app->getUrlManager()->addRules(
            [
                $prefix . '<module:options>/' => '<module>/options/index',
                $prefix . '<module:options>/<controller:options>/' => '<module>/<controller>',
                $prefix . '<module:options>/<controller:options>/<action:\w+>' => '<module>/<controller>/<action>',
                [
                    'pattern' => $prefix . '<module:options>/',
                    'route' => '<module>/options/index',
                    'suffix' => '',
                ], [
                    'pattern' => $prefix . '<module:options>/<controller:options>/',
                    'route' => '<module>/<controller>',
                    'suffix' => '',
                ], [
                    'pattern' => $prefix . '<module:options>/<controller:options>/<action:\w+>',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => '',
                ],
            ],
            true
        );

        // Configure options component
        $app->setComponents([
            'options' => [
                'class' => 'wdmg\options\components\Options'
            ]
        ]);

        // Autoload options from db to app params
        if ($module->autoloadOptions) {
            $component = new Options;
            $component->autoload();
        }
    }
}