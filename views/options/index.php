<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use wdmg\widgets\SelectInput;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel wdmg\options\models\OptionsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $this->context->module->name;
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(<<< JS

    /* To initialize BS3 tooltips set this below */
    $(function () {
        $("[data-toggle='tooltip']").tooltip(); 
    });
    
    /* To initialize BS3 popovers set this below */
    $(function () {
        $("[data-toggle='popover']").popover(); 
    });

JS
);

?>
<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="options-index">
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'param',
                'label' => Yii::t('app/modules/options', 'Label and param'),
                'filter' => true,
                'format' => 'html',
                'value' => function($data) {

                    if ($data->protected)
                        $label = $data->label.' <span title="'.Yii::t('app/modules/options', 'Protected option').'" class="glyphicon glyphicon-lock text-danger"></span>';
                    else
                        $label = $data->label;

                    return $label.'<br/><em class="text-muted">'.$data->getFullParamName().'</em>';
                }
            ],
            [
                'attribute' => 'value',
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->type == 'array') {
                        return 'array()';
                    } else if ($data->type == 'object') {
                        return 'object()';
                    } else {
                        return '<pre contenteditable="true">' . $data->value . '</pre>';
                    }
                }
            ],
            [
                'attribute' => 'default',
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->type == 'array') {
                        return 'array()';
                    } else if ($data->type == 'object') {
                        return 'object()';
                    } else {
                        return $data->default;
                    }
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
                    'items' => $autoloadModes,
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
                'value' => function($data) use ($autoloadModes) {

                    if ($autoloadModes && $data->autoload !== null)
                        $title = $autoloadModes[$data->autoload];
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
                    'update' => function ($model, $key, $index) {
                        return !($model->protected);
                    },
                    'delete' => function ($model, $key, $index) use ($hasAutoload) {
                        return !(($model->autoload && $hasAutoload) || $model->protected);
                    }
                ],
                'buttons'=> [
                    'view' => function($url, $data, $key) use ($module) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::to(['options/view', 'id' => $data['id']]), [
                            'class' => 'option-details-link',
                            'title' => Yii::t('yii', 'View'),
                            'data-toggle' => 'modal',
                            'data-target' => '#optionDetails',
                            'data-id' => $key,
                            'data-pjax' => '1'
                        ]);
                    }
                ],
            ]
        ],
        'rowOptions' => function ($model, $index, $widget, $grid) {
            if($model->value !== $model->default) {
                return ['class' => 'changed warning'];
            }
        },
        'pager' => [
            'options' => [
                'class' => 'pagination',
            ],
            'maxButtonCount' => 5,
            'activePageCssClass' => 'active',
            'prevPageCssClass' => 'prev',
            'nextPageCssClass' => 'next',
            'firstPageCssClass' => 'first',
            'lastPageCssClass' => 'last',
            'firstPageLabel' => Yii::t('app/modules/options', 'First page'),
            'lastPageLabel'  => Yii::t('app/modules/options', 'Last page'),
            'prevPageLabel'  => Yii::t('app/modules/options', '&larr; Prev page'),
            'nextPageLabel'  => Yii::t('app/modules/options', 'Next page &rarr;')
        ],
    ]); ?>
    <hr/>
    <div>
        <div class="btn-group">
            <?= Html::a(Yii::t('app/modules/options', 'Import options'), ['import'], [
                'class' => 'btn btn-warning',
                'data-toggle' => 'modal',
                'data-target' => '#optionsImport',
                'data-pjax' => '1'
            ]) ?>
            <?= Html::a(Yii::t('app/modules/options', 'Export options'), ['export'], [
                'class' => 'btn btn-info',
                'data-pjax' => '0'
            ]) ?>
        </div>
        <?= Html::a(Yii::t('app/modules/options', 'Add new option'), ['create'], ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php $this->registerJs(<<< JS
$('body').delegate('.option-details-link', 'click', function(event) {
    event.preventDefault();
    $.get(
        $(this).attr('href'), 
        function (data) {
            $('#optionDetails .modal-body').html($(data).remove('.modal-footer'));
            if ($(data).find('.modal-footer').length > 0) {
                $('#optionDetails').find('.modal-footer').remove();
                $('#optionDetails .modal-content').append($(data).find('.modal-footer'));
            }
            $('#optionDetails').modal();
        }
    );
});
JS
); ?>

<?php Modal::begin([
    'id' => 'optionsImport',
    'header' => '<h4 class="modal-title">'.Yii::t('app/modules/options', 'Options import').'</h4>',
]); ?>
<?php echo $this->render('_import', ['model' => $importModel]); ?>
<?php Modal::end(); ?>

<?php Modal::begin([
    'id' => 'optionDetails',
    'header' => '<h4 class="modal-title">'.Yii::t('app/modules/options', 'Option details').'</h4>',
    'footer' => '<a href="#" class="btn btn-default pull-left" data-dismiss="modal">'.Yii::t('app/modules/options', 'Close').'</a>',
    'clientOptions' => [
        'show' => false
    ]
]); ?>
<?php Modal::end(); ?>

<?php echo $this->render('../_debug'); ?>
