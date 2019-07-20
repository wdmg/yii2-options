<?php

namespace wdmg\options\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use wdmg\options\components\Options;

use yii\helpers\Console;
use yii\helpers\ArrayHelper;

class InitController extends Controller
{
    /**
     * @inheritdoc
     */
    public $choice = null;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'index';

    public function options($actionID)
    {
        return ['choice', 'color', 'interactive', 'help'];
    }

    public function actionIndex($params = null)
    {
        $module = Yii::$app->controller->module;
        $version = $module->version;
        $welcome =
            '╔════════════════════════════════════════════════╗'. "\n" .
            '║                                                ║'. "\n" .
            '║            OPTIONS  MODULE, v.'.$version.'            ║'. "\n" .
            '║          by Alexsander Vyshnyvetskyy           ║'. "\n" .
            '║         (c) 2019 W.D.M.Group, Ukraine          ║'. "\n" .
            '║                                                ║'. "\n" .
            '╚════════════════════════════════════════════════╝';
        echo $name = $this->ansiFormat($welcome . "\n\n", Console::FG_GREEN);
        echo "Select the operation you want to perform:\n";
        echo "  1) Apply all module migrations\n";
        echo "  2) Revert all module migrations\n";
        echo "  3) Scan and add all application options\n\n";
        echo "Your choice: ";

        if(!is_null($this->choice))
            $selected = $this->choice;
        else
            $selected = trim(fgets(STDIN));

        if ($selected == "1") {
            Yii::$app->runAction('migrate/up', ['migrationPath' => '@vendor/wdmg/yii2-options/migrations', 'interactive' => true]);
        } else if($selected == "2") {
            Yii::$app->runAction('migrate/down', ['migrationPath' => '@vendor/wdmg/yii2-options/migrations', 'interactive' => true]);
        } else if($selected == "3") {
            $count_success = 0;
            $count_fails = 0;
            $component = new Options;

            // Scan and add app params
            foreach (Yii::$app->params as $option => $value) {

                if ($component->set($option, $value, null, null, true, true))
                    $count_success++;
                else
                    $count_fails++;

            }

            // Scan and add modules options
            if(class_exists('\wdmg\admin\models\Modules') && isset(Yii::$app->modules['admin'])) {
                $model = new \wdmg\admin\models\Modules();
                $modules = $model::getModules(true);
                if (is_array($modules)) {
                    foreach ($modules as $module) {

                        $options = (is_array($module['options'])) ? $module['options'] : unserialize($module['options']);
                        unset($options['name']);
                        unset($options['description']);
                        unset($options['controllerNamespace']);
                        unset($options['defaultRoute']);
                        unset($options['routePrefix']);
                        unset($options['vendor']);
                        unset($options['controllerMap']);

                        foreach ($options as $option => $value) {
                            if (is_array($value)) {

                                if ($component->set($module['module'] . '.' . $option, serialize($value), 'array', null, true, false))
                                    $count_success++;
                                else
                                    $count_fails++;

                            } else if (is_object($value)) {

                                if ($component->set($module['module'] . '.' . $option, serialize($value), 'object', null, true, false))
                                    $count_success++;
                                else
                                    $count_fails++;

                            } else if (is_bool($value)) {

                                if ($component->set($module['module'] . '.' . $option, $value, 'boolean', null, true, false))
                                    $count_success++;
                                else
                                    $count_fails++;

                            } else {

                                if ($component->set($module['module'] . '.' . $option, $value, null, null, true, false))
                                    $count_success++;
                                else
                                    $count_fails++;

                            }
                        }
                    }
                }
            }


            echo $this->ansiFormat("\n" . "Options successfully added/updated: {$count_success}, errors: {$count_fails}\n\n", Console::FG_YELLOW);
        } else {
            echo $this->ansiFormat("Error! Your selection has not been recognized.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "\n";
        return ExitCode::OK;
    }
}
