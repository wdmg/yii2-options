<?php

namespace wdmg\options\controllers;

use Yii;
use wdmg\options\models\Options;
use wdmg\options\models\OptionsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * OptionsController implements the CRUD actions for Settings model.
 */
class OptionsController extends Controller
{
    /**
     * Autoload options status
     */
    private $hasAutoload = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $viewed = array();
        $session = Yii::$app->session;

        if(isset($session['viewed-flash']) && is_array($session['viewed-flash']))
            $viewed = $session['viewed-flash'];

        $module = $this->module;
        if($module->autoloadOptions && !in_array('options-has-autoloaded', $viewed) && is_array($viewed)) {
            Yii::$app->getSession()->setFlash(
                'warning',
                Yii::t(
                    'app/modules/options',
                    'Attention! In the module settings, autoloading of application parameters is enabled. The ability to delete parameters with autoloading is limited!'
                )
            );
            $session['viewed-flash'] = array_merge(array_unique($viewed), ['options-has-autoloaded']);
        }
        $this->hasAutoload = $module->autoloadOptions;

        return parent::beforeAction($action);
    }

    /**
     * Lists all Options models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OptionsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'optionsTypes' => $searchModel->optionsTypesList(),
            'autoloadTypes' => $searchModel->autoloadTypesList(),
            'hasAutoload' => $this->hasAutoload
        ]);
    }

    /**
     * Displays a single Option model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Option model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Options();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Option model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'hasAutoload' => $this->hasAutoload
        ]);
    }

    /**
     * Deletes an existing Option model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if($model->autoload && $this->hasAutoload) {
            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/options',
                    'Error! You cannot delete parameter `{param}` because it is used in the startup.',
                    [
                        'param' => $model->param
                    ]
                )
            );
            return $this->redirect(['update', 'id' => $model->id]);
        } else {
            if($model->delete()) {
                Yii::$app->getSession()->setFlash(
                    'success',
                    Yii::t(
                        'app/modules/options',
                        'OK! Parameter `{param}` successfully deleted.',
                        [
                            'param' => $model->param
                        ]
                    )
                );
            } else {
                Yii::$app->getSession()->setFlash(
                    'danger',
                    Yii::t(
                        'app/modules/options',
                        'An error occurred while deleting a parameter `{param}`.',
                        [
                            'param' => $model->param
                        ]
                    )
                );
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Option model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Settings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Options::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/options', 'The requested page does not exist.'));
    }
}
