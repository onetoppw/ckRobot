<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-03-15 21:16
 */

namespace common\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class SearchForm extends \yii\widgets\ActiveForm
{

    public $options = [
        'class' => 'form-inline pull-right'
    ];

    public $action = ['index'];

    public $method = 'get';

    public $fieldClass = 'common\widgets\SearchField';


    /**
     * 生成搜索和重置按钮
     *
     * @param array $options
     * @return string
     */

    public function searchButtons($restUrl = ['index'])
    {
        $html = '<div class="form-group">' . Html::submitButton('<i class="fa fa-search"></i> 搜索', ['class' => 'btn btn-primary btn-sm']);
        if (!empty($restUrl))
            $html .= " " . Html::a('<i class="fa fa-undo"></i> 重置', Url::to($restUrl), ['class' => 'btn btn-default btn-sm']);
        $html .= '</div>';
        return $html;
    }

}
