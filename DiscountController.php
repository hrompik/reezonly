<?php

namespace backend\modules\tariff\controllers;

use backend\modules\tariff\models\TariffDiscount;
use backend\modules\tariff\models\TariffDiscountSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\widgets\ActiveForm;

class DiscountController extends Controller
{
    public $layout = '@common/views/layouts/main';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel  = new TariffDiscountSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGetModel()
    {
        $model = $this->findModel(\Yii::$app->request->post('id', 0));

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $model->getAttributes();
    }

    public function actionPostModel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post                       = Yii::$app->request->post();
        $ajaxValidate               = Yii::$app->request->get('v', '') === 't';
        $id                         = Yii::$app->request->post('id', '');

        if (empty($id)) {
            $model = new TariffDiscount();
        } else {
            $model = $this->findModel($id);
        }

        if ($model->load($post) && !$ajaxValidate && $model->validate()) {
            if (!$model->save()) {
                throw new ServerErrorHttpException('Не удалось сохранить');
            }
        }
        return ActiveForm::validate($model);
    }

    protected function findModel($id)
    {
        if (($model = TariffDiscount::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException();
    }


}
