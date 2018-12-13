<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace api\controllers;

use api\components\Response;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;

class ActiveController extends \yii\rest\ActiveController
{
    public $modelClass = null;

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items'
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;//默认浏览器打开返回json

        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::class,
            'tokenParam' => 'token',
            'except' => ['options']
        ];

        //不知道为什么这里的跨域设置会无效，改用init这里

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Allow-Origin' => ['*'],
                // restrict access to
                'Access-Control-Request-Method' => ['POST', 'GET', 'OPTIONS'],
                // Allow only POST and PUT methods
                'Access-Control-Allow-Headers' => ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'No-Cache', 'If-Modified-Since', 'Last-Modified', 'Cache-Control', 'Expires', 'X-E4M-With'],
                'Access-Control-Request-Headers' => ['*'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Allow-Credentials' => true,
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ];

        return $behaviors;
    }


    public function actions()
    {
        $actions = parent::actions();
        return ['options' => $actions['options']];
    }


}
