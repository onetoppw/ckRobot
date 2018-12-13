<?php
$config = [
    'name' => 'OneTop Admin',
    'version' => '1.0',
    'language' => 'zh-CN',//默认语言
    'timeZone' => 'Asia/Shanghai',//默认时区
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'db' => [//数据库配置，这里的配置可能会被conf/db.local main-local.php配置覆盖
            'class' => yii\db\Connection::class,
            'dsn' => 'mysql:host=localhost;dbname=feehi',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
        'cdn' => [//支持使用 七牛 腾讯云 阿里云 网易云 具体配置请参见 http://doc.feehi.com/cdn.html
            'class' => feehi\cdn\DummyTarget::class,//不使用cdn
        ],
        'cache' => [//缓存组件 具体配置请参考 http://doc.feehi.com/configs.html
            'class' => yii\caching\DummyCache::class,//不使用缓存
        ],
        'log' => [//此项具体详细配置，请访问http://wiki.feehi.com/index.php?title=Yii2_log
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,//当触发levels配置的错误级别时，保存到日志文件
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                    'categories' => ['application'],
                ],
                [
                    'class' => yii\log\FileTarget::class,
                    'categories' => ['client-req'],
                    'levels' => ['error'],
                    'logVars' => [],
                    'logFile' => '@common/logs/error/client-req-' . date('Ym') . '.log',
                ],
                [
                    'class' => yii\log\FileTarget::class,
                    'categories' => ['client-res'],
                    'levels' => ['error'],
                    'logVars' => [],
                    'logFile' => '@common/logs/error/client-res-' . date('Ym') . '.log',
                ],
                [
                    'class' => yii\log\FileTarget::class,
                    'categories' => ['account-user'],
                    'levels' => ['info'],
                    'logVars' => [],
                    'logFile' => '@common/logs/info/account-user-' . date('Ym') . '.log',
                ],
                [
                    'class' => yii\log\FileTarget::class,
                    'categories' => ['account-agent'],
                    'levels' => ['info'],
                    'logVars' => [],
                    'logFile' => '@common/logs/info/account-agent-' . date('Ym') . '.log',
                ],
                [
                    'class' => yii\log\FileTarget::class,
                    'categories' => ['account-system'],
                    'levels' => ['info'],
                    'logVars' => [],
                    'logFile' => '@common/logs/info/account-system-' . date('Ym') . '.log',
                ],
            ],
        ],
        'formatter' => [//格式显示配置
            'dateFormat' => 'php:Y-m-d H:i',
            'decimalSeparator' => '.',
            'thousandSeparator' => ',',
            'currencyCode' => 'CNY',
            'nullDisplay' => '-',
        ],
        'mailer' => [//邮箱发件人配置，会被main-local.php以及后台管理页面中的smtp配置覆盖
            'class' => yii\swiftmailer\Mailer::class,
            'viewPath' => '@common/mail',
            'useFileTransport' => false,//false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.feehi.com',  //每种邮箱的host配置不一样
                'username' => 'admin@feehi.com',
                'password' => 'password',
                'port' => '586',
                'encryption' => 'tls',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['admin@feehi.com' => 'Feehi CMS robot ']
            ],
        ],
        'feehi' => [
            'class' => feehi\components\Feehi::class,
        ],
        'option' => [
            'class' => common\components\Setting::class,
        ],
        'authManager' => [
            'class' => yii\rbac\DbManager::class,
        ],
        'assetManager' => [
            'linkAssets' => false,
            'bundles' => [
                yii\widgets\ActiveFormAsset::class => [
                    'js' => [
                        'a' => 'yii.activeForm.js'
                    ],
                ],
                yii\bootstrap\BootstrapAsset::class => [
                    'css' => [],
                    'sourcePath' => null,
                ],
                yii\captcha\CaptchaAsset::class => [
                    'js' => [
                        'a' => 'yii.captcha.js'
                    ],
                ],
                yii\grid\GridViewAsset::class => [
                    'js' => [
                        'a' => 'yii.gridView.js'
                    ],
                ],
                yii\web\JqueryAsset::class => [
                    'js' => [
                        'a' => 'jquery.js'
                    ],
                ],
                yii\widgets\PjaxAsset::class => [
                    'js' => [
                        'a' => 'jquery.pjax.js'
                    ],
                ],
                yii\web\YiiAsset::class => [
                    'js' => [
                        'a' => 'yii.js'
                    ],
                ],
                yii\validators\ValidationAsset::class => [
                    'js' => [
                        'a' => 'yii.validation.js'
                    ],
                ],
            ],
        ],
    ],
];
$install = yii::getAlias('@common/config/conf/db.php');
if (file_exists($install)) {
    return yii\helpers\ArrayHelper::merge($config, (require $install));
}
return $config;