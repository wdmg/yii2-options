<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model wdmg\options\models\Options */

\yii\web\YiiAsset::register($this);

?>
<div class="options-import">
    <?php $form = ActiveForm::begin([
        'id' => "importOptionsForm",
        'action' => Url::to(['options/import']),
        'method' => 'post',
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>
    <?= $form->field($model, 'import')->fileInput(/*['accept' => 'application/json,application/octet-stream']*/) ?>
    <div class="row">
        <div class="modal-footer" style="clear:both;display:inline-block;width:100%;padding-bottom:0;">
            <?= Html::a(Yii::t('app/modules/options', 'Close'), "#", [
                'class' => 'btn btn-default pull-left',
                'data-dismiss' => 'modal'
            ]) ?>
            <?= Html::submitButton(Yii::t('app/modules/options', 'Import'), ['class' => 'btn btn-success pull-right']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
