<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'language' => 'zh-CN',//默认语言
    'timeZone' => 'Asia/Shanghai',//默认时区
    'homeUrl' => '/api',
    'components' => [
        'user' => [
            'class' => yii\web\User::class,
            'identityClass' => api\models\User::class,
            'loginUrl' => null,
            'enableAutoLogin' => true,
            'enableSession' => false,
        ],
        'log' => [//此项具体详细配置，请访问http://wiki.feehi.com/index.php?title=Yii2_log
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,//当触发levels配置的错误级别时，保存到日志文件
                    'levels' => ['error', 'warning'],
                    'logVars' => ['*'],
                    'categories' => ['application'],
                ]
            ],
        ],
        'cache' => [
            'class' => yii\caching\DummyCache::className(),
            'keyPrefix' => 'api',       // 唯一键前缀
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'register' => 'site/register',
                '<controller:[-\w]+>/<id:\d+>' => '<controller>/<view>',
                '<controller:[-\w]+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:[-\w]+>/<action:[-\w]+>' => '<controller>/<action>',
                'bank/bar-code' => '/bank/bar-code',
                'bank/company-bank' => '/bank/company-bank',
                [
                    'class' => yii\rest\UrlRule::class,
                    'controller' => ['site', 'user'],
                    'only' => ['options'],
                    'patterns' => [
                        '<action>' => 'options'
                    ],
                ],

            ],
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ],
            'enableCsrfValidation' => false,
            'baseUrl' => '/api',
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'class' => 'api\components\Response',
            /*
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },*/
        ],
    ],
    'modules' => [
    ],
    'params' => $params,
];
