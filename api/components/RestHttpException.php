<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace api\components;

use yii\web\HttpException;

class RestHttpException extends HttpException
{
    public function __construct($message = 'server busy', $code = 500)
    {
        parent::__construct($code, $message, $code);
    }
}
