<?php

namespace common\widgets;

use yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

class SearchField extends ActiveField
{
    public $options = [
        'class' => 'form-group'
    ];

    public $labelOptions = [
        'class' => 'control-label',
    ];

    public $template = "{label}\n{input}";


    public function init()
    {
        parent::init();

        if (!isset($this->inputOptions['class'])) {
            $this->inputOptions['class'] = 'form-control';
        }


    }

    /**
     * 数字范围选择
     *
     * @param array $options
     * @return $this
     */
    public function numRange($options = [])
    {
        //if (!isset($options['class'])) $options['class'] = "form-control";
        $options = array_merge($this->inputOptions, $options);
        $this->template = "{label}\n<div class=\"input-group\">{minNumInput}{addon}{maxNumInput}</div>";
        $this->parts['{minNumInput}'] = Html::activeTextInput($this->model, $this->attribute . '_min', $options);
        $this->parts['{addon}'] = html::tag('div', ' ~ ', ['class' => 'input-group-addon']);
        $this->parts['{maxNumInput}'] = Html::activeTextInput($this->model, $this->attribute . '_max', $options);
        return $this;
    }

}