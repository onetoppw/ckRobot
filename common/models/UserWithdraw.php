<?php

namespace common\models;

use common\libs\Constants;
use Exception;
use Yii;
use yii\db\Exception as dbException;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_withdraw}}".
 *
 * @property string $id 存款单号
 * @property string $user_id 用户ID
 * @property string remark 备注
 * @property string $apply_amount 申请取款金额
 * @property int $status 取款状态 1 申请中 2 已完成  0 已取消
 * @property string $transfer_amount 实际转账金额
 * @property int $audit_by_id 处理人员ID
 * @property string $audit_by_username 处理人员
 * @property string $audit_remark 处理备注
 * @property int $audit_at 处理时间
 * @property int $user_bank_id 银行账号ID
 * @property string $bank_name 银行开户名
 * @property string $bank_account 银行账号
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 * @property string $apply_ip 申请时登陆IP
 */
class UserWithdraw extends \yii\db\ActiveRecord
{

    const STATUS_UNCHECKED = 1;
    const STATUS_CHECKED = 2;
    const STATUS_CANCLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_withdraw}}';
    }

    /**
     * @return int
     */
    public static function getUncheckedCount()
    {
        return static::find()->where(['status' => static::STATUS_UNCHECKED])->count('id');
    }

    public static function getStatuses($key = null)
    {
        $status = [
            self::STATUS_UNCHECKED => '申请中',
            self::STATUS_CHECKED => '已完成',
            self::STATUS_CANCLED => '已取消',
        ];
        return $status[$key] ?? $status;
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'apply_amount', 'status'], 'required'],
            [['user_id', 'status', 'audit_by_id', 'audit_at', 'user_bank_id', 'updated_at', 'created_at'], 'integer'],
            [['apply_amount', 'transfer_amount'], 'number'],
            [['remark', 'audit_remark'], 'string', 'max' => 255],
            [['audit_by_username', 'bank_name', 'bank_account', 'apply_ip'], 'string', 'max' => 64],
            [['transfer_amount'], 'checkAmount'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function checkAmount($attribute, $params)
    {
        if ($this->status == 2) {
            if ($this->transfer_amount > $this->apply_amount) {
                $this->addError('transfer_amount', '出款金额不能大于申请金额');
            }

            if ($this->transfer_amount > $this->user->account->frozen_amount) {
                $this->addError('transfer_amount', '出款金额不能大于用户被冻结金额');
            }

        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '存款单号',
            'user_id' => '用户ID',
            'remark' => '备注',
            'apply_amount' => '申请取款金额',
            'status' => '审核状态',
            'transfer_amount' => '出款金额',
            'audit_by_id' => '处理人员ID',
            'audit_by_username' => '处理人员',
            'audit_remark' => '处理备注',
            'audit_at' => '处理时间',
            'user_bank_id' => '银行账号ID',
            'bank_name' => '银行开户名',
            'bank_account' => '银行账号',
            'updated_at' => '更新日期',
            'created_at' => '申请日期',
            'apply_ip' => '申请时登陆IP',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getBank()
    {
        return $this->hasOne(UserBank::class, ['id' => 'user_bank_id', 'user_id' => 'user_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //通过取款申请
        if ($this->status == self::STATUS_CHECKED && $changedAttributes['status'] != self::STATUS_CHECKED) {

            //开始事务
            $tr = Yii::$app->db->beginTransaction();
            try {
                //更新系统资金账户
                $sysAccount = SystemAccount::findOne(['id' => 'K']);
                if (!$sysAccount)
                    throw new dbException('系统资金账户不存在！');

                $sysAccount->available_amount -= $this->transfer_amount;
                if (!$sysAccount->save(false))
                    throw new dbException('更新系统资金账户失败！');

                //增加平台交易记录
                $sysRecord = new SystemAccountRecord();
                $sysRecord->name = "用户取款";
                $sysRecord->trade_no = $this->id;
                $sysRecord->amount = $this->transfer_amount;
                $sysRecord->switch = SystemAccountRecord::SWITCH_OUT;
                $sysRecord->after_amount = $sysAccount->available_amount;
                $sysRecord->remark = $this->remark;
                $sysRecord->confirm_at = time();
                $sysRecord->confirm_by_id = $this->audit_by_id;
                $sysRecord->confirm_by_name = $this->audit_by_username;
                $sysRecord->confirm_remark = $this->audit_remark;
                if (!$sysRecord->save(false))
                    throw new dbException('更新系统资金账户记录失败！');

                $userAccount = UserAccount::findOne(['user_id' => $this->user_id]);
                if (!$userAccount)
                    throw new dbException('用户资金账户不存在！');
                //更新用户额度
                $userAccount->frozen_amount -= $this->apply_amount;
                $userAccount->available_amount += $this->apply_amount - $this->transfer_amount;
                if (!$userAccount->save(false))
                    throw new dbException('更新用户资金账户失败！');
                //添加用户交易记录

                $userRecord = new UserAccountRecord();
                $userRecord->user_id = $this->user_id;
                $userRecord->switch = UserAccountRecord::SWITCH_OUT;
                $userRecord->trade_no = $this->id;
                $userRecord->trade_type_id = Constants::TRADE_TYPE_WITHDRAW;
                $userRecord->remark = $this->remark;
                $userRecord->amount = $this->transfer_amount;
                $userRecord->after_amount = $userAccount->available_amount;
                if (!$userRecord->save(false))
                    throw new dbException('更新用户交易记录失败！');

                $start_time = strtotime(date('Y-m-d 00:00:00'));
                $end_time = strtotime(date('Y-m-d 23:59:59'));
                $user = User::findOne(['id' => $this->user_id]);
                $agent_id = $user->invite_agent_id;
                $amount = $this->transfer_amount;
                $count = UserWithdraw::find()->select('user_id')
                    ->where(['status' => UserWithdraw::STATUS_CHECKED, 'user_id' => $this->user_id])
                    ->andFilterWhere(['between', 'audit_at', $start_time, $end_time])->count();
                if ($count == 1) {
                    Daily::addCounter(['dwu' => 1, 'dwa' => $amount]);
                    AgentDaily::addCounter(['agent_id' => $agent_id, 'dwu' => 1, 'dwa' => $amount]);
                } else {
                    Daily::addCounter(['dwa' => $amount]);
                    AgentDaily::addCounter(['agent_id' => $agent_id, 'dwa' => $amount]);
                }


                $tr->commit();
            } catch (Exception $e) {
                Yii::error($e->getMessage());
                //回滚

                $tr->rollBack();
                $this->setAttributes($changedAttributes);
                $this->save(false);
            }


        }
        //取消取款申请
        if ($this->status == self::STATUS_CANCLED && $changedAttributes['status'] != self::STATUS_CANCLED) {
            $tr = Yii::$app->db->beginTransaction();
            try {
                $userAccount = UserAccount::findOne(['user_id' => $this->user_id]);
                if (!$userAccount)
                    throw new dbException('用户资金账户不存在！');
                //更新用户额度
                $userAccount->frozen_amount -= $this->apply_amount;
                $userAccount->available_amount += $this->apply_amount;
                if (!$userAccount->save(false))
                    throw new dbException('更新用户资金账户失败！');
                $tr->commit();
            } catch (Exception $e) {
                Yii::error($e->getMessage());
                //回滚

                $tr->rollBack();
                $this->setAttributes($changedAttributes);
                $this->save(false);
            }
        }
    }
}
