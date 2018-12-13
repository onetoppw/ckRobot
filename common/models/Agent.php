<?php

namespace common\models;

use common\helpers\FamilyTree;
use common\libs\Constants;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "agent".
 *
 * @property int $id 代理id
 * @property string $username 代理账号
 * @property string $password_hash
 * @property string $auth_key 管理员cookie验证auth_key
 * @property string $password_reset_token 管理员找回密码token
 * @property string $realname 真实姓名
 * @property string $email 邮箱
 * @property string $mobile 手机号码
 * @property string $avatar 头像
 * @property string $promo_code 推广码
 * @property string sub_permission 是否允许下级代理
 * @property int $parent_id 上层账号
 * @property int $top_id 总代账号
 * @property int $agent_level 代理层级
 * @property int $xima_status 洗码是否可看 0 否 1 是
 * @property string $xima_rate 洗码率
 * @property int $xima_type 洗码类别 1 单边 2 双边
 * @property string $rebate_rate 占成
 * @property int $default_player_level 预设玩家层级
 * @property int $rebate_id 返佣方案
 * @property string $available_amount 账户余额
 * @property string $frozen_amount 冻结余额
 * @property string $rebate_amount 返佣总额
 * @property string $currency 主货币
 * @property string $reg_from 创建渠道
 * @property int $reg_time 注册时间
 * @property string $reg_ip 注册IP
 * @property int $status 状态 1：正常 2：冻结 3：锁定 4：注销
 * @property string $memo 备注
 * @property int $created_at 创建日期
 * @property int $updated_at 修改日期
 */
class Agent extends ActiveRecord
{

    const STATUS_NORMAL = 1;
    const STATUS_FROZEN = 2;
    const STATUS_LOCKED = 3;
    const STATUS_DISABLED = 4;

    public $password;

    public $repassword;

    public $old_password;

    public $roles;

    public $permissions;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%agent}}';
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_NORMAL => '正常',
            self::STATUS_FROZEN => '冻结',
            self::STATUS_LOCKED => '锁定',
            self::STATUS_DISABLED => '注销'

        ];
    }

    /**
     * @param int $status
     * @param int $parent_id
     * @param int $level
     * @param bool $self
     * @return array
     */
    public static function getAgentTreeList($status = null, $parent_id = 0, $level = null, $self = false)
    {
        $categories = self::getAgentTree($status, $parent_id, $level, $self);

        //var_dump($categories);
        $data = [];

        $sign = '';
        foreach ($categories as $k => $category) {
            $sign = mb_substr($sign, 0, $category['level'] - 1, 'UTF-8');
            $j = $k + 1;
            while (isset($categories[$j]['level']) && $categories[$j]['level'] > $category['level']) $j++;
            if (isset($categories[$j]['level']) && $categories[$j]['level'] == $category['level']) {
                $name = $sign . '├' . $category['username'];
                $sign .= '│';
            } else {
                $name = $sign . '└' . $category['username'];
                $sign .= ' ';
            }
            $data[$category['id']] = $name;
        }
        return $data;
    }

    /**
     * @param int $status
     * @param int $parent_id
     * @param int $level
     * @param bool $self
     * @return array
     */
    public static function getAgentTree($status = null, $parent_id = 0, $level = null, $self = false)
    {
        $categories = self::_getAgents($status, $level);
        $familyTree = new FamilyTree($categories);
        $array = $familyTree->getDescendants($parent_id, 1, $self);
        return $array;
    }

    /**
     * @param int $status
     * @param int $level
     * @return array|ActiveRecord[]
     */
    protected static function _getAgents($status = null, $level = null)
    {
        return self::find()->orderBy("parent_id asc")
            ->andFilterWhere(['status' => $status])
            ->andFilterWhere(['<', 'agent_level', $level])
            ->asArray()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required', 'on' => ['create']],
            [['username'], 'unique', 'on' => 'create'],
            [['repassword'], 'compare', 'compareAttribute' => 'password'],
            [['parent_id', 'top_id', 'sub_permission', 'agent_level', 'xima_status', 'xima_type', 'default_player_level', 'rebate_id', 'reg_time', 'status', 'created_at', 'updated_at'], 'integer'],
            [['parent_id'], 'checkParent', 'on' => 'create'],
            [['xima_rate', 'rebate_rate', 'available_amount', 'frozen_amount', 'rebate_amount'], 'number'],
            [['username'], 'string', 'max' => 64],
            [['password_hash', 'password_reset_token', 'realname', 'email', 'avatar', 'memo'], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 6],
            [['auth_key', 'mobile', 'promo_code', 'reg_from', 'reg_ip'], 'string', 'max' => 32],
            [['currency'], 'string', 'max' => 16],
            [['username'], 'unique'],
            [['promo_code'], 'unique'],

            [['rebate_rate'], 'compare', 'compareValue' => yii::$app->option->agent_max_rebate * 100, 'operator' => '<='],
            [['xima_rate'], 'compare', 'compareValue' => yii::$app->option->agent_xima_rate * 100, 'operator' => '<='],
            [['rebate_rate', 'xima_rate'], 'filter', 'filter' => function ($value) {
                return $value ? $value / 100 : 0;
            }],
            [['rebate_rate'], 'checkRebateRate'],
            [['xima_rate'], 'checkXimaRate'],


        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '代理id',
            'username' => '代理账号',
            'password_hash' => 'Password Hash',
            'auth_key' => '管理员cookie验证auth_key',
            'password_reset_token' => '管理员找回密码token',
            'realname' => '真实姓名',
            'email' => '邮箱',
            'mobile' => '手机号码',
            'avatar' => '头像',
            'promo_code' => '推广码',
            'sub_permission' => '下级代理权限',
            'parent_id' => '上层账号',
            'top_id' => '总代账号',
            'agent_level' => '代理层级',
            'xima_status' => '查看洗码',
            'xima_rate' => '洗码率',
            'xima_type' => '洗码类别',
            'rebate_rate' => '占成',
            'default_player_level' => '预设玩家层级',
            'rebate_id' => '返佣方案',
            'available_amount' => '账户余额',
            'frozen_amount' => '冻结余额',
            'rebate_amount' => '返佣总额',
            'currency' => '主货币',
            'reg_from' => '创建渠道',
            'reg_time' => '注册时间',
            'reg_ip' => '注册IP',
            'status' => '状态',
            'memo' => '备注',
            'created_at' => '注册日期',
            'updated_at' => '修改日期',
            'password' => '密码',
            'repassword' => '确认密码',
            'old_password' => '旧密码'

        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['username'],
            'update' => ['password', 'realname', 'repassword', 'status', 'rebate_rate', 'xima_status', 'xima_type', 'xima_rate'],
            'create' => ['username', 'realname', 'password', 'repassword', 'parent_id', 'status', 'rebate_rate', 'xima_status', 'xima_type', 'xima_rate'],
            'self-update' => ['password', 'old_password', 'repassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function checkParent($attribute, $params)
    {

        if ($this->parent_id) {
            $parent = $this->getParent()->one();
            if ($parent->sub_permission != 1)
                $this->addError($attribute, '该上级代理不具备发展下级代理权限');
        }
    }

    /**
     * @return Agent|\yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Agent::class, ['id' => 'parent_id']);
    }

    public function checkRebateRate($attribute, $params)
    {

        if ($this->parent_id) {
            $parent = $this->getParent()->one();
            if ($this->rebate_rate > $parent->rebate_rate)
                $this->addError($attribute, '占成不能超出上级代理占成[' . yii::$app->formatter->asPercent($parent->rebate_rate, 2) . ']');
        }
    }

    public function checkXimaRate($attribute, $params)
    {
        if ($this->parent_id) {
            $parent = $this->getParent()->one();
            if ($parent->xima_status == Constants::YesNo_No && $this->xima_status != Constants::YesNo_No) {
                $this->addError('xima_status', '当上级代理洗码不可见时，下级代理也必须设置为洗码不可见');
            }
            if ($parent->xima_type == Constants::XIMA_ONE_SIDED && $this->xima_type != Constants::XIMA_ONE_SIDED) {
                $this->addError('xima_type', '当上级代理类别为单边时，下级代理也必须是单边');
            }

            if (round($this->xima_rate, 4) > round($parent->xima_rate, 4))
                $this->addError($attribute, '洗码率不能超出上级代理洗码率[' . yii::$app->formatter->asPercent($parent->xima_rate, 2) . ']');
        }
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {

        if ($insert) {
            /**
             * @var $parent Agent
             */

            if (!$this->parent_id) {
                $this->agent_level = 1;
            } else {
                $parent = $this->getParent()->one();
                //不允许创建下级代理
                if ($parent->sub_permission != 1) return false;
                if (!$parent->top_id) {
                    $this->top_id = $parent->id;
                } else {
                    $this->top_id = $parent->top_id;
                }

                if ($parent->agent_level < yii::$app->feehi->agent_max_level) {
                    $this->agent_level = (int)$parent->agent_level + 1;
                } else {
                    return false;
                }
            }

            $this->promo_code = static::find()->max('promo_code') + 1;

            $this->generateAuthKey();
            $this->setPassword($this->password);
        } else {
            if (isset($this->password) && $this->password != '') {
                $this->setPassword($this->password);
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $account = new AgentAccount();
            $account->agent_id = $this->id;
            $account->save(false);
        }
    }

    /**
     * @param bool $skipIfSet
     */
    public function loadDefaultValues($skipIfSet = true)
    {

        /*
        $identity = yii::$app->getUser()->getIdentity();
        $this->rebate_rate = $identity->rebate_rate;
        $this->xima_status = $identity->xima_status;
        $this->xima_type = $identity->xima_type;
        $this->xima_rate = $identity->xima_rate;
        */
        parent::loadDefaultValues();
    }

    /**
     * @return Agent|\yii\db\ActiveQuery
     */
    public function getTop()
    {
        return $this->hasOne(Agent::class, ['id' => 'top_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AgentAccount::class, ['agent_id' => 'id']);
    }


}
