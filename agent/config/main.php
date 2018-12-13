<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-agent',//应用id，必须唯一
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'agent\controllers',//控制器命名空间
    'language' => 'zh-CN',//默认语言
    'timeZone' => 'Asia/Shanghai',//默认时区
    'bootstrap' => ['log'],
    'modules' => [],
    'homeUrl' => '/agent',
    'components' => [
        'user' => [
            'identityClass' => agent\models\Agent::class,
            'enableAutoLogin' => false,
            'identityCookie' => ['name' => '_agent_identity'],
            'idParam' => '__agent__id',
            'returnUrlParam' => '_agent_returnUrl',
        ],
        'session' => [
            'timeout' => 1440,//session过期时间，单位为秒
        ],
        'log' => [//此项具体详细配置，请访问http://wiki.feehi.com/index.php?title=Yii2_log
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,//当触发levels配置的错误级别时，保存到日志文件
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request' => [
            'csrfParam' => '_csrf_agent',
            'baseUrl' => '/agent',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false
        ],
        'i18n' => [
            'translations' => [//多语言包设置
                'app*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@backend/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
                'menu' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@backend/messages',
                    'sourceLanguage' => 'zh-CN',
                    'fileMap' => [
                        'app' => 'menu.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
        'assetManager' => [
            'linkAssets' => false,//若为unix like系统这里可以修改成true则创建css js文件软链接到assets而不是拷贝css js到assets目录
            'forceCopy' => false,
        ],
    ],
    /*    'on beforeRequest' => [feehi\components\Feehi::class, 'backendInit'],
        'as access' => [
            'class' => backend\components\AccessControl::class,
            'allowActions' => [
                'site/login',
                'site/captcha',
                'site/error',
                'site/index',
                'site/main',
                'site/logout',
                'site/language',
                'admin-user/request-password-reset',
                'admin-user/reset-password',
                'admin-user/update-self',
                'assets/*',
                'debug/*',
                'gii/*',
            ],
            'superAdminUserIds' => [1],//超级管理员用户id，拥有所有权限，不受权限管理的控制
        ],*/
    'params' => $params,
];
