<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-10-08 00:30
 */

namespace common\clients;

use yii\helpers\Json;

class HjClient extends ClientAbstract
{

    public $sign = "35274a28abbd18857d523912603758d0";
    public $apiHost = "http://api.hj8828.com/api";
    public $loginHost = "http://appapp.gzlwcg.com/login-third.html";

    /**
     * 用户注册
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param \common\models\User $user
     * @return array
     */
    public function register($username, $password, $user)
    {
        $params = [
            'sign' => $this->sign,
            'username' => $username,
            'password' => $password,
            'ratio_switch' => $user->xima_type,
            'ratio' => $user->xima_rate,
            'ratio_setting' => $user->xima_status
        ];
        $url = "{$this->apiHost}/regedit";

        $res = static::get($url, $params);
        if ($res) {
            $res = Json::decode($res);
            if (isset($res['status']) && $res['status'] == 1) return $this->success();
            return $this->error($res['err_msg'] ?? '注册失败', $res);
        } else
            return $this->error('注册失败');
    }


    /**
     * 给用户加分
     *
     * @param $amount
     * @param \common\models\PlatformUser $user
     * @return array
     */
    public function addAmount($amount, $user)
    {
        $amount = (float)$amount;
        $params = [
            'sign' => $this->sign,
            'username' => $user->game_account,
            'password' => $user->game_password,
            'integral' => $amount,
        ];

        $url = "{$this->apiHost}/addintegral";

        $res = static::get($url, $params);
        if ($res) {
            $res = Json::decode($res);
            if (isset($res['status']) && $res['status'] == 1) return $this->success($amount);
            return $this->error($res['err_msg'] ?? '上分失败', $res);
        } else
            return $this->error('上分失败');
    }

    /**
     * 给用户减分
     *
     * @param $amount
     * @param \common\models\PlatformUser $user
     * @return array
     */
    public function reduceAmount($amount, $user)
    {

        $amount = (float)$amount;
        $params = [
            'sign' => $this->sign,
            'username' => $user->game_account,
            'password' => $user->game_password,
            'integral' => $amount,
        ];
        $url = "{$this->apiHost}/reduceintegral";
        $res = static::get($url, $params);
        if ($res) {
            $res = Json::decode($res);
            if (isset($res['status']) && $res['status'] == 1) return $this->success($amount);
            return $this->error($res['err_msg'] ?? '下分失败', $res);
        } else
            return $this->error('下分失败');
    }

    /**
     * 查询用户分数
     *
     * @param \common\models\PlatformUser $user
     * @return array
     */
    public function queryAmount($user)
    {
        $params = [
            'sign' => $this->sign,
            'username' => $user->game_account,
        ];
        $url = "{$this->apiHost}/query";
        $res = static::get($url, $params);
        if ($res) {
            $res = Json::decode($res);
            if (isset($res['status']) && $res['status'] == 1) return $this->success($res['integral']);
            return $this->error($res['err_msg'] ?? '查询用户分数失败', $res);
        } else
            return $this->error('查询用户分数失败');
    }

    /**
     * 代理下属所有用户投注数据报表
     *
     * @param string $begindate 开始日期
     * @param string $enddate 开始日期
     * @return mixed
     */
    public function betList($begindate, $enddate)
    {
        $begin = strtotime($begindate);
        $end = strtotime($enddate);
        $days = ceil(($end - $begin) / 86400);
        if ($days > 7 || $days < 0) {
            return false;
        }
        $params = [
            'sign' => $this->sign,
            'begindate' => date('Y-m-d H:i:s', $begin),
            'enddate' => date('Y-m-d H:i:s', $end)
        ];
        $url = "{$this->apiHost}/betlist";

        $res = static::get($url, $params);
        if ($res) {
            $res = Json::decode($res);
            if (isset($res['status']) && $res['status'] == 1) return $this->success($res['bet_list']);
            return $this->error($res['err_msg'] ?? '代理下属所有用户投注数据报表失败', $res);
        } else
            return $this->error('代理下属所有用户投注数据报表失败');
    }


    /**
     * 获取登陆url
     *
     * @param \common\models\PlatformUser $user
     * @param null $redirectUrl
     * @return array
     */
    public function login($user, $redirectUrl = null)
    {

        $password = md5(md5($user->game_password));

        return $this->success($this->loginHost . '?loginUrl=' . urlencode($redirectUrl) . '&username=' . $user->game_account . '&password=' . $password . '&sign=' . $this->sign);
    }

}