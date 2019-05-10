<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\settings\models\Settings */

$this->title = Yii::t('app/modules/settings', 'Create Settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/settings', 'Settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
