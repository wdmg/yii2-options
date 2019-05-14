<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model wdmg\options\models\Settings */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/options', 'Options'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="options-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'label',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->protected)
                        return $data->label.' <span title="'.Yii::t('app/modules/options', 'Protected option').'" class="glyphicon glyphicon-lock text-danger"></span>';
                    else
                        return $data->label;
                }
            ],
            [
                'attribute' => 'param',
                'format' => 'ntext',
                'value' => function($data) {
                    return $data->getFullParamName();
                }
            ],
            'value:ntext',
            'default:ntext',
            [
                'attribute' => 'type',
                'format' => 'html',
                'value' => function($data) use ($optionsTypes) {
                    if ($optionsTypes && $data->type !== null)
                        return $optionsTypes[$data->type];
                    else
                        return $data->type;
                },
            ],
            [
                'attribute' => 'autoload',
                'format' => 'html',
                'value' => function($data) use ($autoloadModes) {
                    if ($autoloadModes && $data->autoload !== null)
                        return $autoloadModes[$data->autoload];
                    else
                        return $data->autoload;
                },
            ]
        ],
    ]) ?>
    <div class="modal-footer">
        <?= Html::a(Yii::t('app/modules/options', 'Close'), "#", [
                'class' => 'btn btn-default pull-left',
                'data-dismiss' => 'modal'
        ]) ?>
        <?php
            if (!($model->protected))
                echo Html::a(Yii::t('app/modules/options', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary pull-right']);
        ?>
        <?php
            if (!(($model->autoload && $hasAutoload) || $model->protected))
                echo Html::a(Yii::t('app/modules/options', 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger pull-right',
                    'data' => [
                        'confirm' => Yii::t('app/modules/options', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]);
        ?>
    </div>
</div>
