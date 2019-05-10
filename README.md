[![Progress](https://img.shields.io/badge/required-Yii2_v2.0.13-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Github all releases](https://img.shields.io/github/downloads/wdmg/yii2-settings/total.svg)](https://GitHub.com/wdmg/yii2-settings/releases/)
[![GitHub version](https://badge.fury.io/gh/wdmg/yii2-settings.svg)](https://github.com/wdmg/yii2-settings)
![Progress](https://img.shields.io/badge/progress-in_development-red.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-settings.svg)](https://github.com/wdmg/yii2-settings/blob/master/LICENSE)

# Yii2 Settings Module
Module storage application settings in the database for Yii2

# Requirements
* PHP 5.6 or higher
* Yii2 v.2.0.13 and newest

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-settings"`

After configure db connection, run the following command in the console:

`$ php yii settings/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-settings/migrations`

# Configure
To add a module to the project, add the following data in your configuration file:

    'components' => [
        ...
        'settings' => [
            'class' => 'wdmg\settings\components\Settings'
        ],
        ...
    ],
    'modules' => [
        ...
        'settings' => [
            'class' => 'wdmg\settings\Module',
            'routePrefix' => 'admin'
        ],
        ...
    ],

If you have connected the module not via a composer add Bootstrap section:

`
$config['bootstrap'][] = 'wdmg\settings\Bootstrap';
`

# Usage

    <?php
        $settings = Yii::$app->settings;
        $value1 = $settings->get('system.test1');
        $value2 = $settings->get('test2', 'system');
        $value3 = $settings->get('test3');
    ?>
    

# Routing
Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('settings')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [in progress development]
* v.1.0.1 - Added settings component
* v.1.0.0 - Added base migrations