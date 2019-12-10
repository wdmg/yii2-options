<?php

namespace wdmg\options\controllers;

use Yii;
use wdmg\options\models\Options;
use wdmg\options\models\OptionsSearch;
use wdmg\options\models\OptionsImport;
use wdmg\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

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
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                    'view' => ['get'],
                    'delete' => ['post'],
                    'create' => ['get', 'post'],
                    'update' => ['get', 'post'],
                    'export' => ['get'],
                    'import' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];

        // If auth manager not configured use default access control
        if(!Yii::$app->authManager) {
            $behaviors['access'] = [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true
                    ],
                ]
            ];
        }

        return $behaviors;
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
        $importModel = new OptionsImport();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'importModel' => $importModel,
            'dataProvider' => $dataProvider,
            'optionsTypes' => $searchModel->getOptionsTypeList(),
            'autoloadModes' => $searchModel->getAutoloadModeList(),
            'hasAutoload' => $this->hasAutoload,
            'module' => $this->module
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
        $model = $this->findModel($id);
        return $this->renderAjax('view', [
            'model' => $model,
            'optionsTypes' => $model->getOptionsTypeList(),
            'autoloadModes' => $model->getAutoloadModeList(),
            'hasAutoload' => $this->hasAutoload
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
        $model->autoload = 0;
        $model->protected = 0;

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->value)
                    return $this->asJson(['success' => true, 'type' => $model->getTypeByValue($model->value)]);
                else
                    return $this->asJson(['success' => false]);
            }
        } else {
            if ($model->load(Yii::$app->request->post())) {
                if($model->save()) {
                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t(
                            'app/modules/options',
                            'OK! Parameter `{param}` successfully added.',
                            [
                                'param' => $model->getFullParamName()
                            ]
                        )
                    );
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/options',
                            'An error occurred while adding a parameter `{param}`.',
                            [
                                'param' => $model->getFullParamName()
                            ]
                        )
                    );
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'optionsTypes' => $model->getOptionsTypeList(false),
            'autoloadModes' => $model->getAutoloadModeList(false)
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
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if($model->protected) {
            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/options',
                    'Error! You cannot edit a protected option `{param}`.',
                    [
                        'param' => $model->getFullParamName()
                    ]
                )
            );
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && !$model->protected) {


            if ($model->type == 'array' || $model->type == 'object') {

                if (!is_array($model->value))
                    $values = unserialize($model->value);
                else
                    $values = $model->value;

                $post = Yii::$app->request->post('DynamicModel');
                $attributes = self::getFlattenAttributes($values);
                $arrayModel = self::buildDynamicModel($attributes);

                $data = [];
                foreach ($attributes as $name => $param) {
                    if (in_array($name, array_keys($attributes)) && isset($post[$name])) {
                        $value = $post[$name];
                        $type = gettype(ArrayHelper::getValue($values, $name));
                        if ($type == 'bool' || $type == 'boolean') {
                            $value = ($post[$name] == '1') ? true : false;
                            if ($arrayModel->validate($name))
                                ArrayHelper::setValue($data, $name, $value);
                        } else {
                            settype($value, $type);
                            if ($arrayModel->validate($name))
                                ArrayHelper::setValue($data, $name, $value);
                        }
                    }
                }

                $values = ArrayHelper::merge($values, $data);
                if ($serialized = serialize($values)) {
                    $model->value = $serialized;
                }
            }

            if ($model->save()) {
                Yii::$app->getSession()->setFlash(
                    'success',
                    Yii::t(
                        'app/modules/options',
                        'OK! Parameter `{param}` successfully edited.',
                        [
                            'param' => $model->getFullParamName()
                        ]
                    )
                );
            } else {
                Yii::$app->getSession()->setFlash(
                    'danger',
                    Yii::t(
                        'app/modules/options',
                        'An error occurred while editing a parameter `{param}`.',
                        [
                            'param' => $model->getFullParamName()
                        ]
                    )
                );
            }
            return $this->redirect(['index']);
        }

        if ($model->type == 'array' || $model->type == 'object') {

            if (!is_array($model->value))
                $values = unserialize($model->value);
            else
                $values = $model->value;

            $attributes = self::getFlattenAttributes($values);
            $arrayModel = self::buildDynamicModel($attributes);
            $arrayModel->setAttributes($attributes);
            $arrayModel->validate();
            $model->model = $arrayModel;
        }

        return $this->render('update', [
            'model' => $model,
            'optionsTypes' => $model->getOptionsTypeList(false),
            'autoloadModes' => $model->getAutoloadModeList(false),
            'hasAutoload' => $this->hasAutoload
        ]);
    }

    private function buildDynamicModel($attributes = null) {

        if (!is_array($attributes))
            return false;

        $columns = array_keys($attributes);
        $model = new \yii\base\DynamicModel($columns);
        foreach ($attributes as $attribute => $value) {
            $type = gettype($value);
            if ($type == 'array' || $type == 'object') {
                $model->addRule($attribute, 'string');
            } else {
                $model->addRule($attribute, $type);
            }
        }
        return $model;
    }

    private function getFlattenAttributes($attributes, $recursive = false, $origin = null) {

        if (is_null($origin))
            $origin = $attributes;

        foreach ($attributes as $attribute => $value) {
            if (is_array($value)) {
                unset($attributes[$attribute]);
                $attributes = array_merge($attributes, self::getFlattenAttributes($value, true, $origin));
            } else {

                if ($recursive) {
                    unset($attributes[$attribute]);
                    $attribute = ArrayHelper::getParents($attribute, $origin);
                }

                $attributes = array_merge($attributes, [$attribute => $value]);
            }
        }
        return $attributes;
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

        if($model->protected) {
            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/options',
                    'Error! You cannot delete a protected from deletion option `{param}`.',
                    [
                        'param' => $model->param
                    ]
                )
            );
            return $this->redirect(['index']);
        } elseif ($model->autoload && $this->hasAutoload) {
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
            return $this->redirect(['index']);
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

    public function actionExport() {
        $filename = 'options_'.date('dmY_His').'.json';
        $options = Options::find()->select('section, label, param, value, type, autoload, protected')->asArray()->all();
        Yii::$app->response->sendContentAsFile(Json::encode($options), $filename, [
            'mimeType' => 'application/json',
            'inline' => false
        ])->send();
    }

    public function actionImport() {
        $model = new OptionsImport();
        if (Yii::$app->request->isPost) {
            if($model->validate()) {
                $import = UploadedFile::getInstance($model, 'import');
                $options = file_get_contents($import->tempName);
                if ($data = Json::decode($options)) {
                    if ($model->import($data)) {
                        Yii::$app->getSession()->setFlash(
                            'success',
                            Yii::t(
                                'app/modules/options',
                                'OK! Parameters successfully imported/updated.'
                            )
                        );
                    }
                } else {
                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/options',
                            'An error occurred while importing/updating parameters.'
                        )
                    );
                }
            }
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the Option model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return the loaded model
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
