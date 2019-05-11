<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\options\models\Settings */

$this->title = Yii::t('app/modules/options', 'Create Settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/options', 'Settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="options-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
