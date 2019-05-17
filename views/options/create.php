<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\users\models\Options */

$this->title = Yii::t('app/modules/options', 'Create option');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/options', 'All options'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="options-create">
    <?= $this->render('_form', [
        'model' => $model,
        'optionsTypes' => $optionsTypes,
        'autoloadModes' => $autoloadModes
    ]) ?>
</div>