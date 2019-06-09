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
            foreach (Yii::$app->params as $param => $value) {
                if($component->set($param, $value, null, null, true, true))
                    $count_success++;
                else
                    $count_fails++;
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
