<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-10-08 00:30
 */

namespace common\clients;


use yii\helpers\Json;


class JxbClient extends ClientAbstract
{

    public $apiHost = "http://103.231.167.85:8888";


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

        $data['username'] = $username;
        $data['password'] = $password;
        $res = static::get("{$this->apiHost}/user/register", $data);

        if ($res) {
            $res = Json::decode($res);
            if (isset($res['code']) && $res['code'] == 0) return $this->success(['account_id' => $res['data']['id'] ?? null]);
            return $this->error($res['message'] ?? '注册失败', $res);
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
        $url = "{$this->apiHost}/admin/backend/points";

        $data['userid'] = $user->game_account_id;
        $data['score'] = $amount;
        $data['type'] = 0;
        $res = static::get($url, $data);

        if ($res) {
            $res = Json::decode($res);
            if (isset($res['code']) && $res['code'] == 0) return $this->success($amount);
            return $this->error($res['message'] ?? '上分失败', $res);
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
        $url = "{$this->apiHost}/admin/backend/points";
        $data['userid'] = $user->game_account_id;
        $data['score'] = $amount;
        $data['type'] = 1;
        $res = static::get($url, $data);

        if ($res) {
            $res = Json::decode($res);
            if (isset($res['code']) && $res['code'] == 0) return $this->success($amount);
            return $this->error($res['message'] ?? '下分失败', $res);
        } else
            return $this->error('下分失败');
    }

    /**
     * 查询用户分数
     *
     * @param \common\models\PlatformUser $user
     * @return mixed
     */
    public function queryAmount($user)
    {

        $url = "{$this->apiHost}/admin/backend/user";
        //$authData = $user->decodeAuthData();
        $data['userid'] = $user->game_account_id;
        $res = static::get($url, $data);
        if ($res) {
            $res = Json::decode($res);
            if (isset($res['code']) && $res['code'] == 0) return $this->success($res['data']['availableAmount'] ?? 0);
            return $this->error($res['message'] ?? '查询分数失败', $res);
        } else
            return $this->error('查询分数失败');
    }

    /**
     * 代理下属所有用户投注数据报表
     *
     * @param int $begindate 开始日期
     * @param int $enddate 开始日期
     * @return array
     */
    public function betList($begindate, $enddate)
    {
        $data['beginTime'] = $begindate * 1000;
        $data['endTime'] = $enddate * 1000;
        $url = "{$this->apiHost}/admin/backend/user/gamerecord/list";
        $res = static::get($url, $data);
        if ($res) {
            $res = Json::decode($res);
            if (isset($res['code']) && $res['code'] == 0) return $this->success($res['data'] ?? []);
            return $this->error($res['message'] ?? '代理下属所有用户投注数据报表失败', $res);
        } else
            return $this->error('代理下属所有用户投注数据报表失败');

    }


    /**
     * 用户登陆
     *
     * @param \common\models\PlatformUser $user
     * @param null $redirectUrl
     * @return array
     */
    public function login($user, $redirectUrl = null)
    {
        $post['username'] = $user->game_account;
        $post['password'] = $user->game_password;
        $res = static::post("{$this->apiHost}/user/login", $post);
        if ($res) {
            $res = Json::decode($res);
            if (isset($res['code']) && $res['code'] == 0) return $this->success($res['data']);
            return $this->error($res['message'] ?? '登录失败', $res);
        } else
            return $this->error('登录失败');
    }

}