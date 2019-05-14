<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\options\models\Settings */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="options-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'param')->textInput(['maxlength' => true, 'value' => $model->getFullParamName()])->hint(Yii::t('app/modules/options', 'Use the symbol «.» as the delimiter of the group and parameter.'), ['class' => 'hint-block']) ?>
    <?= $form->field($model, 'value')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'default')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'type')->widget(SelectInput::className(), [
        'items' => $optionsTypes,
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>

    <?= $form->field($model, 'autoload')->widget(SelectInput::className(), [
        'items' => $autoloadModes,
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>

    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/options', '&larr; Back to list'), ['options/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?= Html::submitButton(Yii::t('app/modules/options', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
