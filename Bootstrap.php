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
        if (isset($module->routePrefix)) {
            $app->getUrlManager()->enableStrictParsing = true;
            $prefix = $module->routePrefix . '/';
        } else {
            $prefix = '';
        }

        // Add module URL rules
        $app->getUrlManager()->addRules(
            [
                $prefix . '<module:options>' => '<module>/options/index',
                $prefix . '<module:options>/<controller:\w+>' => '<module>/<controller>',
                $prefix . '<module:options>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>' => '<module>/<controller>/<action>',
                $prefix . '<module:options>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>' => '<module>/<controller>/<action>',
                [
                    'pattern' => $prefix . '<module:options>/',
                    'route' => '<module>/options/index',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:options>/<controller:\w+>/',
                    'route' => '<module>/<controller>',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:options>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:options>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>/',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => ''
                ]
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