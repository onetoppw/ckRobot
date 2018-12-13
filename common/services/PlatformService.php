<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-10-08 00:30
 */

namespace common\services;


use common\clients\ClientAbstract;
use common\models\PlatformUser;
use yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\db\Exception;
use yii\db\Exception as dbException;

class PlatformService extends BaseObject
{

    /**
     * @var $client \common\clients\ClientAbstract
     */
    public $client;

    /**
     * @var $model \common\models\PlatformUser
     */
    public $model;

    /**
     * 游戏类型
     * @var $gameType string
     */
    public $gameType;

    /**
     * 用户ID
     * @var $userId int
     */
    public $userId;


    /**
     * 尝试注册次数
     * @var int
     */
    public $registerTryTimes = 5;


    public function init()
    {
        parent::init();
        if ($this->model instanceof PlatformUser) {
            $this->gameType = $this->model->platform_code;
            $this->userId = $this->model->user_id;
        }
    }

    /**
     * 返回用户登陆信息
     * @param null $redirectUrl 重定向地址
     * @param int $amount 上分值，-1不上分，0上所有分，>0上具体分数
     * @return bool|mixed
     */
    public function getLoginInfo($redirectUrl = null, $amount = 0)
    {

        $model = $this->getModel();
        //注册用户
        if (!$model) $this->register();

        //登陆
        $res = $this->getClient()->login($model, $redirectUrl);
        if ($res['error'] != '') {
            throw new InvalidCallException($res['error']);
        }
        //开始上分
        if ($amount > -1) {
            try {
                $this->addAmount($amount);
            } catch (Exception $e) {
            }
        }

        $model->last_login_at = time();
        $model->last_login_ip = yii::$app->request->getUserIP();
        $model->save(false);
        return $res['data'];
    }

    /**
     * @return PlatformUser|null
     *
     */
    public function getModel()
    {
        if (!$this->model) {
            if (!$this->gameType || !$this->userId) {
                throw new InvalidArgumentException('游戏类型或用户ID必须设置');
            }
            $this->model = PlatformUser::findOne(['platform_code' => $this->gameType, 'user_id' => $this->userId]);
        }
        return $this->model;
    }

    /**
     * 调用平台注册接口注册用户
     * @return bool
     */
    public function register()
    {
        if ($this->getModel()) return true;

        $model = new PlatformUser(['platform_code' => $this->gameType, 'user_id' => $this->userId]);

        if (!$model->platform) {
            throw new InvalidArgumentException('错误的游戏类型');
        }
        if (!$model->user) {
            throw new InvalidArgumentException('错误的用户ID');
        }

        //生成随机密码
        $password = yii::$app->security->generateRandomString(8);
        $loop = 0;
        $res['error'] = '';
        do {
            if ($loop > $this->registerTryTimes) {
                if ($res['error'])
                    throw new InvalidCallException($res['error']);
                throw new InvalidCallException('账号注册失败！');
            }
            //生成用户名
            $username = $model->user->username . ($loop ? rand(100, 999) : '');

            $res = $this->getClient()->register($username, $password, $model->user);
            if ($res['error'] == '') {
                if (isset($res['data']['account_id'])) {
                    $model->game_account_id = $res['data']['account_id'];
                }
                $model->platform_id = $model->platform->id;
                $model->username = $model->user->username;
                $model->game_account = $username;
                $model->game_password = $password;
                $model->first_login_ip = yii::$app->request->getUserIP();

                if (!$model->save(false)) {
                    throw new InvalidCallException('保存平台账户失败！');
                }
                return true;
            }

            $loop++;
        } while ($loop);

    }

    public function getClient()
    {
        if (!$this->client instanceof ClientAbstract) {
            $clients = yii::$app->params['clients'];
            if (!isset($clients[strtoupper($this->gameType)]))
                return false;
            $this->client = yii::createObject($clients[strtoupper($this->gameType)]);
            if (!$this->client instanceof ClientAbstract)
                return false;
        }
        return $this->client;
    }

    /**
     * 上分
     * @param int $amount
     * @return bool|int
     */
    public function addAmount($amount = 0)
    {
        if ($amount < 0)
            throw new InvalidArgumentException('上分额度不能小于0');

        $model = $this->getModel();
        if (!$model)
            throw new InvalidArgumentException('平台用户账户不存在！');

        $account = $model->user->account;

        if ($amount == 0)
            $amount = (int)$account->available_amount;
        if ($amount > $account->available_amount)
            throw new InvalidArgumentException('上分额度不能大于用户现有额度！');

        $tr = Yii::$app->db->beginTransaction();
        try {

            $account->available_amount -= $amount;
            if (!$account->save(false))
                throw new dbException('更新用户账户失败！');
            $model->available_amount += $amount;
            if (!$model->save(false))
                throw new dbException('更新用户平台账户失败！');
            $res = $this->getClient()->addAmount($amount, $model);
            if ($res['error'] != '') {
                throw new InvalidCallException($res['error']);
            }
            $tr->commit();
            return $amount;
        } catch (\Exception $e) {
            //回滚
            $tr->rollBack();
            throw new InvalidCallException($e->getMessage());
        }
    }

    /**
     * 回收分数，0为回收平台所有分数
     * @param int $amount
     * @return int
     */
    public function getAmount($amount = 0)
    {
        if ($amount < 0)
            throw new InvalidArgumentException('回收分数额度不能小于0');

        $model = $this->getModel();
        if (!$model)
            throw new InvalidArgumentException('平台用户账户不存在！');


        $qamount = $this->queryAmount();
        if ($amount == 0) {
            $amount = $qamount;
        } elseif ($amount > $qamount) {
            throw new InvalidArgumentException('平台可回收分数额度小于:' . $amount);
        }

        $res = $this->getClient()->reduceAmount($amount, $model);
        if ($res['error'] != '') {
            throw new InvalidCallException($res['error']);
        }
        if ($res['data'] == 0) return 0;

        $tr = Yii::$app->db->beginTransaction();
        try {
            $account = $model->user->account;
            $account->available_amount += $res['data'];
            if (!$account->save(false))
                throw new dbException('更新用户账户失败！');
            $model->available_amount = $qamount - $res['data'];
            if (!$model->save(false))
                throw new dbException('更新用户平台账户失败！');
            $tr->commit();
            return $res['data'];

        } catch (\Exception $e) {
            @$this->getClient()->addAmount($res['data'], $model);
            $tr->rollBack();
            throw new InvalidCallException($e->getMessage());
        }
    }

    /**
     * 查询分数
     */
    public function queryAmount()
    {
        $model = $this->getModel();
        if (!$model)
            throw new InvalidArgumentException('平台用户账户不存在！');
        $res = $this->getClient()->queryAmount($model);
        if ($res['error'] != '') {
            throw new InvalidCallException($res['error']);
        }
        return $res['data'];
    }

}