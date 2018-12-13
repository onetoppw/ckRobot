<?php

namespace common\models;

use common\libs\Constants;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as dbException;

/**
 * This is the model class for table "{{%change_amount_record}}".
 *
 * @property int $id
 * @property string $user_id 用户ID
 * @property int $switch 上下分 1 上分 2 下分
 * @property string $amount 额度
 * @property string $after_amount 余额
 * @property int $status 审核状态
 * @property string $remark 备注
 * @property int $submit_by_id 提交者ID
 * @property string $submit_by_name 提交者名称
 * @property int $audit_by_id 审核人员ID
 * @property string $audit_by_name 审核人员
 * @property string $audit_remark 审核备注
 * @property int $audit_at 审核时间
 * @property int $created_at 创建时间
 * @property int $updated_at 最后修改时间
 */
class ChangeAmountRecord extends \yii\db\ActiveRecord
{
    const SWITCH_UP = 1;
    const SWITCH_DOWN = 2;
    const STATUS_UNCHECKED = 1;
    const STATUS_CHECKED = 2;
    const STATUS_CANCLED = 0;

    public $username;
    public $available_amount;
    public $amount_min;
    public $amount_max;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%change_amount_record}}';
    }

    /**
     * @return array
     */
    public static function getSwitchs($key = null)
    {
        $ary = [
            self::SWITCH_UP => '上分',
            self::SWITCH_DOWN => '下分',
        ];
        return $ary[$key] ?? $ary;
    }

    public static function getStatuses($key = null)
    {
        $status = [
            self::STATUS_UNCHECKED => '待审核',
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
            [['user_id', 'switch', 'amount'], 'required', 'on' => 'create'],
            [['id', 'user_id', 'switch', 'status', 'submit_by_id', 'audit_by_id', 'audit_at', 'created_at', 'updated_at'], 'integer'],
            [['amount', 'after_amount'], 'number'],
            [['remark', 'audit_remark'], 'string', 'max' => 255],
            [['submit_by_name', 'audit_by_name'], 'string', 'max' => 64],
            [['amount'], 'checkAmount', 'on' => 'create'],
            [['status'], 'required', 'on' => 'audit'],
            [['id'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function scenarios()
    {
        return [
            'create' => ['switch', 'available_amount', 'amount', 'remark'],
            'audit' => ['status', 'remark'],
        ];
    }

    public function checkAmount($attribute, $params)
    {
        if ($this->switch) {
            if ($this->switch == 1) {
                if (yii::$app->option->finance_add_amount_max > 0 && $this->amount > yii::$app->option->finance_add_amount_max)
                    $this->addError($attribute, '上分额度超出系统限制最大额度：' . yii::$app->option->finance_add_amount_max);
            } else if ($this->switch == 2) {
                if (yii::$app->option->finance_reduce_amount_max > 0 && $this->amount > yii::$app->option->finance_reduce_amount_max)
                    $this->addError($attribute, '下分额度超出系统限制最大额度：' . yii::$app->option->finance_reduce_amount_max);
                if ($this->amount > $this->available_amount)
                    $this->addError($attribute, '下分额度超出当前用户可用额度' . $this->available_amount);
            }

        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'switch' => '上下分',
            'amount' => '额度',
            'after_amount' => '余额',
            'status' => '审核状态',
            'remark' => '备注',
            'submit_by_id' => '提交者ID',
            'submit_by_name' => '提交者名称',
            'audit_by_id' => '审核人员ID',
            'audit_by_name' => '审核人员',
            'audit_remark' => '审核备注',
            'audit_at' => '审核时间',
            'created_at' => '创建时间',
            'updated_at' => '最后修改时间',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function beforeSave($insert)
    {
        if (!$insert)
            return parent::beforeSave($insert);

        $admin = yii::$app->getUser()->getIdentity();
        $account = UserAccount::findOne(['user_id' => $this->user_id]);
        //上分
        if ($this->switch == 1) {
            //待审核
            if (yii::$app->option->finance_add_amount_open_aduit) {
                $this->status = 1;
                $account->frozen_amount += $this->amount;
            } else {
                $this->status = 2;
                $account->available_amount += $this->amount;
                $this->audit_by_id = $admin->id;
                $this->audit_by_name = $admin->username;
                $this->audit_at = time();
                $this->audit_remark = '自动审核';
            }
            $this->after_amount = $account->available_amount;

        } else if ($this->switch == 2) //下分
        {
            $account->available_amount -= $this->amount;
            if (yii::$app->option->finance_reduce_amount_open_aduit) {
                $this->status = 1;
                $account->frozen_amount += $this->amount;
            } else {
                $this->status = 2;
                $this->audit_by_id = $admin->id;
                $this->audit_by_name = $admin->username;
                $this->audit_remark = '自动审核';
                $this->audit_at = time();
            }
            $this->after_amount = $account->available_amount;

        } else {
            return false;
        }

        $this->submit_by_id = $admin->id;
        $this->submit_by_name = $admin->username;

        if (!$account->save(false)) {
            return false;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            if ($this->status == 2) {
                $record = new UserAccountRecord();
                $record->user_id = $this->user_id;
                $record->switch = $this->switch;
                $record->trade_no = $this->id;
                $record->trade_type_id = $this->switch == 1 ? Constants::TRADE_TYPE_ADMINADD : Constants::TRADE_TYPE_ADMINREDUCE;
                $record->remark = $this->remark;
                $record->amount = $this->amount;
                $record->after_amount = $this->after_amount;
                $record->save(false);
            }
        } else {
            //通过上下分申请
            if ($this->status == self::STATUS_CHECKED && $changedAttributes['status'] != self::STATUS_CHECKED) {

                //开始事务
                $tr = Yii::$app->db->beginTransaction();
                try {

                    $userAccount = UserAccount::findOne(['user_id' => $this->user_id]);
                    if (!$userAccount)
                        throw new dbException('用户资金账户不存在！');
                    //更新用户额度
                    $userAccount->frozen_amount -= $this->amount;
                    if ($this->switch == self::SWITCH_UP)
                        $userAccount->available_amount += $this->amount;
                    if (!$userAccount->save(false))
                        throw new dbException('更新用户资金账户失败！');
                    //添加用户交易记录

                    $userRecord = new UserAccountRecord();
                    $userRecord->user_id = $this->user_id;
                    $userRecord->switch = $this->switch;
                    $userRecord->trade_no = $this->id;
                    $userRecord->trade_type_id = $this->switch == 1 ? Constants::TRADE_TYPE_ADMINADD : Constants::TRADE_TYPE_ADMINREDUCE;
                    $userRecord->remark = $this->remark;
                    $userRecord->amount = $this->amount;
                    $userRecord->after_amount = $userAccount->available_amount;
                    if (!$userRecord->save(false))
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
            //取消上下分申请
            if ($this->status == self::STATUS_CANCLED && $changedAttributes['status'] != self::STATUS_CANCLED) {
                $tr = Yii::$app->db->beginTransaction();
                try {
                    $userAccount = UserAccount::findOne(['user_id' => $this->user_id]);
                    if (!$userAccount)
                        throw new dbException('用户资金账户不存在！');
                    //更新用户额度
                    $userAccount->frozen_amount -= $this->amount;
                    if ($this->switch == self::SWITCH_DOWN)
                        $this->available_amount += $this->amount;
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
}
