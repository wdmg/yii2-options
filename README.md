[![Progress](https://img.shields.io/badge/required-Yii2_v2.0.13-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Github all releases](https://img.shields.io/github/downloads/wdmg/yii2-options/total.svg)](https://GitHub.com/wdmg/yii2-options/releases/)
[![GitHub version](https://badge.fury.io/gh/wdmg/yii2-options.svg)](https://github.com/wdmg/yii2-options)
![Progress](https://img.shields.io/badge/progress-in_development-red.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-options.svg)](https://github.com/wdmg/yii2-options/blob/master/LICENSE)

# Yii2 Options Module
Storage application options (settings) in the database for Yii2

# Requirements
* PHP 5.6 or higher
* Yii2 v.2.0.13 and newest
* [Yii2 SelectInput](https://github.com/wdmg/yii2-selectinput) widget

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-options"`

After configure db connection, run the following command in the console:

`$ php yii options/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations
  3) Scan and add all application options

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-options/migrations`

# Configure
To add a module to the project, add the following data in your configuration file:

    'components' => [
        ...
        'options' => [
            'class' => 'wdmg\options\components\Options'
        ],
        ...
    ],
    'modules' => [
        ...
        'options' => [
            'class' => 'wdmg\options\Module',
            'autoloadOptions' => true,
            'routePrefix' => 'admin'
        ],
        ...
    ],

If you have connected the module not via a composer add Bootstrap section:

`
$config['bootstrap'][] = 'wdmg\options\Bootstrap';
`

# Usage

    <?php
        
        // Get app options (default)
        $options = Yii::$app->params;
        $email = $options['adminEmail'];
        
        // Get app options (from DB)
        $options = Yii::$app->options;
        $email = $options->get('adminEmail');
        $tokenExpire = $options->get('user.passwordResetTokenExpire');
        // or
        $email = $options->adminEmail;
        $tokenExpire = $options->user->passwordResetTokenExpire;
        
        // Set app options
        $options = Yii::$app->options;
        $options->set('adminEmail', "admin@example.com");
        $options->set('user.passwordResetTokenExpire', 3600);
        // or
        $options->adminEmail = "admin@example.com";
        //$options->user->passwordResetTokenExpire = 3600; //@TODO Emplement later
        
    ?>
    

# Routing
Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('options')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [in progress development]
* v.1.3.0 - Added import/export interface
* v.1.2.6 - Added autodetect option type if create new