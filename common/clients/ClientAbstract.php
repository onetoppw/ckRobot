<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-10-08 00:30
 */

namespace common\clients;

use common\helpers\Util;
use Yii;
use yii\base\BaseObject;

abstract class  ClientAbstract extends BaseObject
{
    protected $_error;

    public static function post($url, $data = null, $header = false, $timeout = 30)
    {
        return static::request($url, $data, $header, $timeout);

    }

    public static function request($url, $data = null, $header = false, $timeout = 30)
    {
        $res = util::request($url, $data, $header, $timeout);
        if ($res['code'] == 0) return $res['result'];
        static::reqLog('[' . $url . '] request failed!code:' . $res['code'] . ',msg:' . $res['msg'] . ',data:' . json_encode($data) . ',info:' . json_encode($res['info']));
        return false;
    }

    public static function reqLog($message)
    {
        Yii::error($message, 'client-req');
    }

    public static function get($url, $data = null, $header = false, $timeout = 30)
    {
        $url .= (strpos($url, '?') ? '&' : '?') . http_build_query($data);
        return static::request($url, null, $header, $timeout);
    }

    /**
     * 接口返回成功数据格式
     * @param $data
     * @return array
     */
    public function success($data = null)
    {
        return ['error' => '', 'data' => $data];
    }

    /**
     * 接口返回错误数据格式
     * @param $message
     * @param array|string $res
     * @return array
     */
    public function error($message, $res = [])
    {
        if (!$message) $message = 'error';
        if (!empty($res)) {
            $backtrace = debug_backtrace();
            array_shift($backtrace);
            $backtrace = $backtrace[0];
            static::resLog('[' . $backtrace['class'] . '->' . $backtrace['function'] . '(' . implode(',', $backtrace['args']) . ')] response failed! message:' . $message . ' res:' . json_encode($res));
        }

        return ['error' => $message, 'data' => ''];
    }

    public static function resLog($message)
    {
        Yii::error($message, 'client-res');
    }

    /**
     * 登陆
     * @param \common\models\PlatformUser $user
     * @param string $redirectUrl
     * @return array
     */
    abstract public function login($user, $redirectUrl = null);

    /**
     * 注册
     * @param $username
     * @param $password
     * @param \common\models\User $user
     * @return array
     */
    abstract public function register($username, $password, $user);

    /**
     * 查询分数
     * @param \common\models\PlatformUser $user
     * @return array
     */
    abstract public function queryAmount($user);

    /**
     * 上分
     * @param $amount
     * @param \common\models\PlatformUser $user
     * @return array
     */
    abstract public function addAmount($amount, $user);

    /**
     * 下分
     * @param $amount
     * @param \common\models\PlatformUser $user
     * @return array
     */
    abstract public function reduceAmount($amount, $user);


}