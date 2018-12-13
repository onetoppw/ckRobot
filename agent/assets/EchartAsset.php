<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-03-15 21:16
 */

namespace agent\assets;

use yii;

class EchartAsset extends yii\web\AssetBundle
{

    public $sourcePath = '@bower/echarts/dist';
    public $js = [
        'echarts.common.min.js',
    ];

}
