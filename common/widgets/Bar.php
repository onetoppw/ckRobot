<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2016-06-15 09:25
 */

namespace common\widgets;

use yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

class Bar extends Widget
{
    public $buttons = [];

    public $options = [
        'class' => 'toolbar-actions m-t-md',
    ];
    public $template = "{refresh} {create} {delete}";


    /**
     * @inheritdoc
     */
    public function run()
    {
        $buttons = '';
        $this->initDefaultButtons();
        $buttons .= $this->renderDataCellContent();
        return html::tag('div', $buttons, $this->options);
    }

    /**
     * 生成默认按钮
     *
     */
    protected function initDefaultButtons()
    {
        if (!isset($this->buttons['refresh'])) {
            $this->buttons['refresh'] = function () {
                return Html::a('<i class="fa fa-refresh"></i> ' . yii::t('app', 'Refresh'), Url::to(['refresh']), [
                    'title' => yii::t('app', 'Refresh'),
                    'data-pjax' => '0',
                    'class' => 'btn btn-white btn-sm refresh',
                ]);
            };
        }

        if (!isset($this->buttons['create'])) {
            $this->buttons['create'] = function () {
                return Html::a('<i class="fa fa-plus"></i> ' . yii::t('app', 'Create'), Url::to(['create']), [
                    'title' => yii::t('app', 'Create'),
                    'data-pjax' => '0',
                    'class' => 'btn btn-primary btn-sm',
                ]);
            };
        }

        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function () {
                return Html::a('<i class="fa fa-trash-o"></i> ' . yii::t('app', 'Delete'), Url::to(['delete']), [
                    'title' => yii::t('app', 'Delete'),
                    'data-pjax' => '0',
                    'data-confirm' => yii::t('app', 'Really to delete?'),
                    'class' => 'btn btn-danger btn-sm multi-operate',
                ]);
            };
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent()
    {
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) {
            $name = $matches[1];
            if (isset($this->buttons[$name])) {
                return $this->buttons[$name] instanceof \Closure ? call_user_func($this->buttons[$name]) : $this->buttons[$name];
            } else {
                return '';
            }


        }, $this->template);
    }
}