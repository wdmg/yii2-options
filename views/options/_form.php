<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\options\models\Options */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="options-form">
    <?php $form = ActiveForm::begin([
        'id' => "addOptionForm",
        'enableAjaxValidation' => true
    ]); ?>
    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'param')->textInput(['maxlength' => true, 'value' => $model->getFullParamName()])->hint(Yii::t('app/modules/options', 'Use the symbol «.» as the delimiter of the group and parameter.'), ['class' => 'hint-block']) ?>
    <?php

        if ($model->type == 'array' || $model->type == 'object') {

            echo Html::label(Yii::t('app/modules/options', 'Value'), '#innerOptionDatails');
            if ($model->hasModel($model->model)) {
                $attributes = [];
                $innerModel = $model->model;
                foreach ($innerModel as $name => $value) {
                    $attributes[] = [
                        'label' => $name, // $innerModel->generateAttributeLabel($name),
                        'captionOptions' => ['style' => 'font-weight: normal !important;'],
                        'format' => 'raw',
                        'value' => function() use ($form, $innerModel, $name, $value) {

                            $type = gettype($innerModel->$name);
                            if ($type == 'bool' || $type == 'boolean') {
                                return $form->field($innerModel, $name)->checkBox([
                                    'label' => Yii::t('app/modules/options', '- check the box to activate the option'),
                                    'labelOptions' => [
                                        'style' => 'font-weight: normal !important;'
                                    ],
                                    'checked'=> ($value) ? true : false,
                                    'value' => '1'
                                ]);
                            } else {
                                return $form->field($innerModel, $name)->textInput(['maxlength' => true, 'value' => $value])->label(false);
                            }

                        }
                    ];
                }
                echo yii\widgets\DetailView::widget([
                    'id' => 'innerOptionDatails',
                    'model' => $innerModel,
                    'attributes' => $attributes
                ]);
            }

        } elseif ($model->type == 'boolean') {
            echo Html::label(Yii::t('app/modules/options', 'Value'));
            echo $form->field($model, 'value')->checkBox([
                'label' => Yii::t('app/modules/options', '- check the box to activate the option'),
                'labelOptions' => [
                    'style' => 'font-weight: normal !important;'
                ],
                'selected' => $model->value
            ]);
        } else {
            echo $form->field($model, 'value')->textarea(['rows' => 6]);
        }

    ?>
    <?php
        if ($model->type == 'array' || $model->type == 'object') {
            echo Html::label(Yii::t('app/modules/options', 'Default'), '#innerOptionDefault');
            echo '<pre>' . var_export(unserialize($model->default), true) . '</pre>';
        } else {
            echo $form->field($model, 'default')->textarea(['rows' => 6, 'disabled' => true]);
        }
    ?>
    <?php

        if ($model->id) {
            echo $form->field($model, 'type')->widget(SelectInput::class, [
                'items' => $optionsTypes,
                'options' => [
                    'class' => 'form-control',
                    'disabled' => "disabled"
                ]
            ]);
        } else {
            echo $form->field($model, 'type')->widget(SelectInput::class, [
                'items' => $optionsTypes,
                'options' => [
                    'class' => 'form-control'
                ]
            ]);
        }

    ?>
    <?= $form->field($model, 'autoload')->widget(SelectInput::class, [
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