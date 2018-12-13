<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2016-03-21 19:32
 */

namespace agent\grid;

use Yii;
use yii\helpers\Html;
use yii\web\View;

/**
 * @inheritdoc
 */
class NumberColumn extends DataColumn
{
    public $headerOptions = ['width' => '120px'];

    public $filter = "default";

    public $layerOptions = [];

    public $filterInputOptions = ["class" => "form-control date-time"];


    public function init()
    {
        parent::init();
    }

    protected function renderFilterCellContent()
    {

        return Html::activeTextInput($this->grid->filterModel, $this->attribute, $this->filterInputOptions) . $error;
    }
}