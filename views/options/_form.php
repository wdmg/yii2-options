<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\options\models\Settings */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="options-form">
    <?php $form = ActiveForm::begin([
        'id' => "addOptionForm",
        'enableAjaxValidation' => true
    ]); ?>
    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'param')->textInput(['maxlength' => true, 'value' => $model->getFullParamName()])->hint(Yii::t('app/modules/options', 'Use the symbol «.» as the delimiter of the group and parameter.'), ['class' => 'hint-block']) ?>
    <?= $form->field($model, 'value')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'default')->textarea(['rows' => 6]) ?>
    <?php

        if ($model->id) {
            echo $form->field($model, 'type')->widget(SelectInput::className(), [
                'items' => $optionsTypes,
                'options' => [
                    'class' => 'form-control',
                    'disabled' => "disabled"
                ]
            ]);
        } else {
            echo $form->field($model, 'type')->widget(SelectInput::className(), [
                'items' => $optionsTypes,
                'options' => [
                    'class' => 'form-control'
                ]
            ]);
        }

    ?>
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
<?php $this->registerJs(<<< JS
$(document).ready(function() {
    function afterValidateAttribute(event, attribute, messages)
    {
        if (attribute.name == "value" && attribute.status == 1 && messages.length == 0) {
            var form = $(event.target);
            $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serializeArray(),
                }
            ).done(function(data) {
                if(data.success) {
                    if(data.type) {
                        form.find('#options-type').val(data.type);
                        form.find('#options-type').trigger('change');
                    }
                } else {
                    form.find('#options-type').val("string");
                    form.find('#options-type').trigger('change');
                }
                form.yiiActiveForm('validateAttribute', 'options-type');
            }).fail(function () {
                form.find('#options-type').val("");
                form.find('#options-type').trigger('change');
            });
            return false; // prevent default form submission
        }
    }
    $("#addOptionForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>