<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $searchModel wdmg\options\models\OptionsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/modules/options', 'Options');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="options-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app/modules/options', 'Create option'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'param',
                'label' => Yii::t('app/modules/options', 'Label and param'),
                'filter' => true,
                'format' => 'html',
                'value' => function($data) {
                    if ($data->section)
                        return $data->label.'<br/><em class="text-muted">'.$data->section.'.'.$data->param.'</em>';
                    else
                        return $data->label.'<br/><em class="text-muted">'.$data->param.'</em>';
                }
            ],
            [
                'attribute' => 'value',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->value)
                        return '<pre contenteditable="true">'.$data->value.'</pre>';
                }
            ],
            [
                'attribute' => 'default',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->default)
                        return '<pre class="text-muted">'.$data->default.'</pre>';
                }
            ],
            [
                'attribute' => 'type',
                'format' => 'html',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'type',
                    'items' => $optionsTypes,
                    'options' => [
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
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
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'autoload',
                    'items' => $autoloadTypes,
                    'options' => [
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) use ($autoloadTypes) {

                    if ($autoloadTypes && $data->type !== null)
                        $title = $autoloadTypes[$data->autoload];
                    else
                        $title = '';

                    if ($data->autoload)
                        return '<span title="'.$title.'" class="glyphicon glyphicon-check text-success"></span>';
                    else
                        return '<span title="'.$title.'" class="glyphicon glyphicon-check text-muted"></span>';
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => function ($model, $key, $index) use ($hasAutoload) {
                        return !($model->autoload && $hasAutoload);
                    }
                ]
            ]
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
