<?php

namespace backend\modules\crm\controllers;

use backend\modules\crm\models\ClientDomains;
use backend\modules\crm\models\ClientDomainSearch;
use common\actions\domain\ChangeAutoProlongAction;
use common\actions\domain\ChangeEgaisAction;
use common\actions\domain\PriceInfoAction;
use common\actions\domain\ProlongAction;
use common\components\websales\Websales;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DomainController extends Controller
{
    public $layout = '@common/views/layouts/main';

    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => ['@'],
                        ],
                    ],
                ],
            ]
        );
    }

    public function actions()
    {
        return [
            'change-egais'        => [
                'class' => ChangeEgaisAction::class,
            ],
            'change-auto-prolong' => [
                'class' => ChangeAutoProlongAction::class,
            ],
            'price-info'          => [
                'class' => PriceInfoAction::class,
            ],
            'prolong'             => [
                'class' => ProlongAction::class,
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel  = new ClientDomainSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $domain = $this->findModel($id);

        $info = (new Websales($domain))->getInfoAndUpdate();

        /** @var ClientDomains $domain */
        $domain = ClientDomains::find()
            ->joinWith([
                'client',
                'points.tariff',
                'points.discount',
                'networks.points.tariff',
                'networks.points.discount',
            ])
            ->andWhere(['client_domains.id' => $id])
            ->one()
        ;

        return $this->render('view', [
            'client' => $domain->client,
            'domain' => $domain,
            'info'   => $info,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = ClientDomains::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException();
    }
}
