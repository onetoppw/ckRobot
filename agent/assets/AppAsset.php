<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-03-15 21:16
 */

namespace agent\assets;

use yii;

class AppAsset extends \yii\web\AssetBundle
{

    public $sourcePath = '@agent/web/static/';
    public $css = [
        'css/bootstrap.min14ed.css?v=3.3.6',
        'css/font-awesome.min93e3.css?v=4.4.0',
        'css/animate.min.css',
        'css/style.min862f.css?v=4.1.0',
        'js/plugins/layer/laydate/theme/default/laydate.css',
        'css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
        'css/plugins/toastr/toastr.min.css',
        'css/daterangepicker.css',
        'css/agent.css',

    ];

    public $js = [
        'js/feehi.js',
        'js/plugins/layer/laydate/laydate.js',
        'js/plugins/layer/layer.min.js',
        'js/plugins/prettyfile/bootstrap-prettyfile.js',
        'js/moment.js',
        'js/daterangepicker.js',
        'js/plugins/toastr/toastr.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
