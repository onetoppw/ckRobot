<?php

namespace common\models;

use common\libs\Constants;
use Yii;
use yii\behaviors\TimestampBehavior;
use Exception;
use yii\db\Exception as dbException;

/**
 * This is the model class for table "{{%agent_withdraw}}".
 *
 * @property string $id 取款单号
 * @property string $agent_id 代理ID
 * @property string $apply_amount 申请取款金额
 * @property int $status 取款状态 1 申请中 2 已完成  0 已取消
 * @property string $transfer_amount 实际转账金额
 * @property string $remark 备注信息
 * @property int $audit_by_id 处理人员ID
 * @property string $audit_by_username 处理人员
 * @property string $audit_remark 处理备注
 * @property int $audit_at 处理时间
 * @property int $agent_bank_id 银行账号ID
 * @property string $bank_name 银行开户名
 * @property string $bank_account 银行账号
 * @property string $apply_ip 申请时登陆IP
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class AgentWithdraw extends \yii\db\ActiveRecord
{
    const STATUS_UNCHECKED = 1;
    const STATUS_CHECKED = 2;
    const STATUS_CANCLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%agent_withdraw}}';
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['agent_id', 'status', 'audit_by_id', 'audit_at', 'agent_bank_id', 'updated_at', 'created_at'], 'integer'],
            [['apply_amount', 'agent_bank_id'], 'required'],
            [['apply_amount', 'transfer_amount'], 'number'],
            [['remark', 'audit_remark'], 'string', 'max' => 255],
            [['audit_by_username', 'bank_name', 'bank_account', 'apply_ip'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '取款单号',
            'agent_id' => '代理ID',
            'apply_amount' => '申请取款金额',
            'status' => '取款状态',//'取款状态 1 申请中 2 已完成  0 已取消',
            'transfer_amount' => '实际转账金额',
            'remark' => '备注信息',
            'audit_by_id' => '处理人员ID',
            'audit_by_username' => '处理人员',
            'audit_remark' => '处理备注',
            'audit_at' => '处理时间',
            'agent_bank_id' => '银行账号ID',
            'bank_name' => '银行开户名',
            'bank_account' => '银行账号',
            'apply_ip' => '申请时登陆IP',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(Agent::class, ['id' => 'agent_id']);
    }

    public function getBank()
    {
        return $this->hasOne(AgentBank::class, ['id' => 'agent_bank_id', 'agent_id' => 'agent_id']);
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
                $sysRecord->name = "代理取款";
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

                $agentAccount = AgentAccount::findOne(['agent_id' => $this->agent_id]);
                if (!$agentAccount)
                    throw new dbException('代理用户资金账户不存在！');
                //更新用户额度
                $agentAccount->frozen_amount -= $this->apply_amount;
                $agentAccount->available_amount += $this->apply_amount - $this->transfer_amount;
                if (!$agentAccount->save(false))
                    throw new dbException('更新用户资金账户失败！');
                //添加用户交易记录

                $agentRecord = new AgentAccountRecord();
                $agentRecord->agent_id = $this->agent_id;
                $agentRecord->switch = AgentAccountRecord::SWITCH_OUT;
                $agentRecord->trade_no = $this->id;
                $agentRecord->trade_type_id = Constants::TRADE_TYPE_WITHDRAW;
                $agentRecord->remark = $this->remark;
                $agentRecord->amount = $this->transfer_amount;
                $agentRecord->after_amount = $agentAccount->available_amount;
                if (!$agentRecord->save(false))
                    throw new dbException('更新用户交易记录失败！');

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
                $agentAccount = AgentAccount::findOne(['agent_id' => $this->agent_id]);
                if (!$agentAccount)
                    throw new dbException('用户资金账户不存在！');
                //更新用户额度
                $agentAccount->frozen_amount -= $this->apply_amount;
                $agentAccount->available_amount += $this->apply_amount;
                if (!$agentAccount->save(false))
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
